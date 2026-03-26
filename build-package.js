const fs = require("fs");
const path = require("path");
const archiver = require("archiver");

// ================= CONFIG =================
const pluginSlug = "giftflow";
const zipFileName = `${pluginSlug}.zip`;

/**
 * Blacklist root files / folders
 */
const blacklistRoots = [
  "node_modules",
  ".git",
  ".github",
  ".gitignore",
  ".DS_Store",
  ".vscode",
  ".idea",
  ".env",
  ".env.local",
  ".env.development.local",
  ".env.test.local",
  ".husky",
  "package-lock.json",
  "phpcs.xml.dist",
  "vendor",          
  "giftflow.zip",
];

/**
 * Blacklist extensions
 */
const blacklistExtensions = [
  ".sh",
  ".DS_Store",
];

/**
 * Blacklist inside vendor-prefixed
 */
const vendorPrefixedBlacklist = [
  /\/tests?\//i,
  /\/docs?\//i,
  /\/examples?\//i,
  /CODEGEN_VERSION/i,
];

// =========================================

// Setup zip
const output = fs.createWriteStream(zipFileName);
const archive = archiver("zip", { zlib: { level: 9 } });

archive.pipe(output);

archive.on("error", err => {
  throw err;
});

output.on("close", () => {
  console.log(`✅ ${zipFileName} created (${archive.pointer()} bytes)`);
});

// Helpers
function normalize(p) {
  return p.replace(/\\/g, "/");
}

function isBlacklisted(relativePath) {
  const p = normalize(relativePath);

  // Root blacklist
  if (blacklistRoots.some(root => p === root || p.startsWith(root + "/"))) {
    return true;
  }

  // Extension blacklist
  if (blacklistExtensions.includes(path.extname(p))) {
    return true;
  }

  // vendor-prefixed internal blacklist
  if (
    p.startsWith("vendor-prefixed/") &&
    vendorPrefixedBlacklist.some(rx => rx.test(p))
  ) {
    return true;
  }

  return false;
}

function walk(dir) {
  fs.readdirSync(dir).forEach(entry => {
    const fullPath = path.join(dir, entry);
    const relPath = normalize(path.relative(".", fullPath));

    if (isBlacklisted(relPath)) return;

    const stat = fs.statSync(fullPath);
    const archivePath = path.posix.join("giftflow", relPath);
    if (stat.isDirectory()) {
      walk(fullPath);
    } else {
      archive.file(fullPath, { name: archivePath });
    }
  });
}

// Build
walk(".");
archive.finalize();
