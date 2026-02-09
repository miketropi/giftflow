/**
 * GiftFlow Repeater Field Class
 * 
 * Handles dynamic repeater fields with add/remove functionality.
 * 
 * @example
 * // Initialize via PHP inline script:
 * new GiftFlowRepeaterField({
 *   repeaterId: 'my-repeater',
 *   maxRows: 10,
 *   rowTemplate: '<div class="giftflow-repeater-row">...</div>',
 *   rowLabel: 'Item'
 * });
 */
class GiftFlowRepeaterField {
  /**
   * @param {Object} config - Configuration options
   * @param {string} config.repeaterId - The repeater container element ID
   * @param {number} config.maxRows - Maximum number of rows allowed (0 = unlimited)
   * @param {string} config.rowTemplate - HTML template for new rows (use __INDEX__ as placeholder)
   * @param {string} config.rowLabel - Label text for row titles
   */
  constructor(repeaterElement, config) {
    this.config = {
      repeaterId: '',
      maxRows: 0,
      rowTemplate: '',
      rowLabel: 'Row',
      ...config
    };
    // console.log(this.config.rowTemplate);
    this.$ = jQuery;
    this.$repeater = this.$(repeaterElement);
    this.$rows = null;
    this.$addButton = null;
    this.$hiddenInput = null;

    this.init();
  }

  /**
   * Initialize the repeater field
   */
  init() {
    // this.$repeater = this.$(`#${this.config.repeaterId}`);
    
    if (!this.$repeater.length) {
      console.warn(`GiftFlowRepeaterField: Element #${this.config.repeaterId} not found`);
      return;
    }

    this.cacheElements();
    this.bindEvents();
  }

  /**
   * Cache DOM elements for performance
   */
  cacheElements() {
    this.$rows = this.$repeater.find('.giftflow-repeater-rows');
    this.$addButton = this.$repeater.find('.giftflow-repeater-add-row');
    this.$hiddenInput = this.$repeater.find('input[type=hidden]');
  }

  /**
   * Bind event handlers
   */
  bindEvents() {
    this.$addButton.on('click', this.handleAddRow.bind(this));
    this.$repeater.on('click', '.giftflow-repeater-remove-row', this.handleRemoveRow.bind(this));
    this.$repeater.on('change', 'input, select, textarea', this.handleFieldChange.bind(this));
  }

  /**
   * Handle add row button click
   * @param {Event} e - Click event
   */
  handleAddRow(e) {
    e.preventDefault();

    if (this.isMaxRowsReached()) {
      return;
    }

    const newIndex = this.getRowCount();
    const newRow = this.$(this.config.rowTemplate.replace(/__INDEX__/g, newIndex));
    console.log(newRow);
    this.$rows.append(newRow);
    this.updateHiddenInput();
    this.updateAddButtonState();
  }

  /**
   * Handle remove row button click
   * @param {Event} e - Click event
   */
  handleRemoveRow(e) {
    e.preventDefault();

    const $row = this.$(e.currentTarget).closest('.giftflow-repeater-row');
    $row.remove();

    this.reindexRows();
    this.updateHiddenInput();
    this.updateAddButtonState();
  }

  /**
   * Handle field value change
   */
  handleFieldChange() {
    this.updateHiddenInput();
  }

  /**
   * Check if maximum rows limit is reached
   * @returns {boolean}
   */
  isMaxRowsReached() {
    return this.config.maxRows > 0 && this.getRowCount() >= this.config.maxRows;
  }

  /**
   * Get current number of rows
   * @returns {number}
   */
  getRowCount() {
    return this.$rows.children('.giftflow-repeater-row').length;
  }

  /**
   * Update the add button disabled state
   */
  updateAddButtonState() {
    this.$addButton.prop('disabled', this.isMaxRowsReached());
  }

  /**
   * Reindex all rows after removal
   */
  reindexRows() {
    const self = this;
    
    this.$rows.find('.giftflow-repeater-row').each(function(index) {
      const $row = self.$(this);
      
      $row.attr('data-index', index);
      $row.find('.giftflow-repeater-row-title').text(`${self.config.rowLabel} ${index + 1}`.replace('__INDEX__', index + 1));
      
      $row.find('input, select, textarea').each(function() {
        const $field = self.$(this);
        const name = $field.attr('name');
        
        if (name) {
          $field.attr('name', name.replace(/\[\d+\]/, `[${index}]`));
        }
      });
    });
  }

  /**
   * Update hidden input with serialized row values
   */
  updateHiddenInput() {
    const values = this.collectValues();
    this.$hiddenInput.val(JSON.stringify(values));
  }

  /**
   * Collect all row values
   * @returns {Array<Object>}
   */
  collectValues() {
    const self = this;
    const values = [];

    this.$rows.find('.giftflow-repeater-row').each(function() {
      const rowValues = self.collectRowValues(self.$(this));
      values.push(rowValues);
    });

    return values;
  }

  /**
   * Collect values from a single row
   * @param {jQuery} $row - The row element
   * @returns {Object}
   */
  collectRowValues($row) {
    const self = this;
    const rowValues = {};

    $row.find('input, select, textarea').each(function() {
      const $field = self.$(this);
      const name = $field.attr('name');
      const matches = name ? name.match(/\[(\d+)\]\[([^\]]+)\]/) : null;

      if (matches) {
        const fieldId = matches[2];
        const fieldType = $field.attr('type');

        if (fieldType === 'checkbox' || fieldType === 'switch') {
          rowValues[fieldId] = $field.is(':checked');
        } else {
          rowValues[fieldId] = $field.val();
        }
      }
    });

    return rowValues;
  }

  /**
   * Destroy the repeater instance and unbind events
   */
  destroy() {
    this.$addButton.off('click');
    this.$repeater.off('click', '.giftflow-repeater-remove-row');
    this.$repeater.off('change', 'input, select, textarea');
  }
}

// Export for module systems or attach to window for global access
export default GiftFlowRepeaterField;

// if (typeof module !== 'undefined' && module.exports) {
//   module.exports = GiftFlowRepeaterField;
// } else {
//   window.GiftFlowRepeaterField = GiftFlowRepeaterField;
// }