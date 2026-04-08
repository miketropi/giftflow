import React from 'react';
import { MousePointerClick, Settings, TrendingUp } from 'lucide-react';

const Welcome = () => {
  const admin = typeof giftflow_admin !== 'undefined' ? giftflow_admin : {};
  const base = admin.admin_url || '';
  const create_campaign_url = `${base}post-new.php?post_type=campaign`;
  const settings_url = `${base}admin.php?page=giftflow-settings`;
  const docs_url = admin.docs_url || 'https://giftflow-doc.beplus-agency.cloud/';
  const support_url = admin.support_url || 'https://giftflow.beplus-agency.cloud/contact';

  let doc_host = 'giftflow-doc.beplus-agency.cloud';
  try {
    doc_host = new URL(docs_url).hostname;
  } catch {
    // keep default
  }

  const features = [
    {
      icon: MousePointerClick,
      title: 'Create and launch new fundraising campaigns',
      description: 'in just a few clicks.',
    },
    {
      icon: Settings,
      title: 'Customize plugin settings',
      description: "to match your organization's needs.",
    },
    {
      icon: TrendingUp,
      title: 'Track campaign progress',
      description: 'and donor engagement from your dashboard.',
    },
  ];

  return (
    <header className="giftflow-dashboard-view__masthead" aria-labelledby="giftflow-dashboard-heading">
      <div className="giftflow-dashboard-view__masthead-main">
        <div className="giftflow-dashboard-view__masthead-badge">
          <span className="giftflow-dashboard-view__masthead-badge-dot" aria-hidden="true" />
          GiftFlow
        </div>
        <h2 id="giftflow-dashboard-heading" className="giftflow-dashboard-view__masthead-title">
          Dashboard
        </h2>
        <p className="giftflow-dashboard-view__masthead-lead">
          Your hub for managing fundraising campaigns, donations, and plugin settings.
        </p>
        <div className="giftflow-dashboard-view__masthead-actions">
          <a className="button button-primary" href={create_campaign_url}>
            <span className="dashicons dashicons-plus-alt" aria-hidden="true" />
            Create campaign
          </a>
          <a className="button" href={settings_url}>
            <span className="dashicons dashicons-admin-settings" aria-hidden="true" />
            Settings
          </a>
        </div>
        <p className="giftflow-dashboard-view__masthead-meta">
          <span className="giftflow-dashboard-view__masthead-meta-label">Docs</span>
          <a href={docs_url} target="_blank" rel="noopener noreferrer">
            {doc_host}
          </a>
          {' · '}
          <a href={support_url} target="_blank" rel="noopener noreferrer">
            Contact support
          </a>
        </p>
      </div>
      <div className="giftflow-dashboard-view__masthead-deco">
        <div className="giftflow-dashboard-view__masthead-deco-grid" aria-hidden="true" />
        <div className="giftflow-dashboard-view__masthead-deco-glow" aria-hidden="true" />
        <div className="giftflow-dashboard-view__masthead-aside">
          <h3>Key features</h3>
          {features.map((feature, index) => (
            <div className="giftflow-dashboard-view__feature-item" key={index}>
              <feature.icon width={18} height={18} strokeWidth={2} aria-hidden="true" />
              <div>
                <strong>{feature.title}</strong> {feature.description}
              </div>
            </div>
          ))}
          <div className="giftflow-dashboard-view__help-callout">
            Need more detail? Browse the{' '}
            <a href={docs_url} target="_blank" rel="noopener noreferrer">
              documentation
            </a>{' '}
            or{' '}
            <a href={support_url} target="_blank" rel="noopener noreferrer">
              reach the team
            </a>
            .
          </div>
        </div>
      </div>
    </header>
  );
};

export default Welcome;
