/**
 * SelectSearch Component
 * 
 * A customizable, searchable select dropdown component with keyboard navigation,
 * loading states, error handling, and accessibility features.
 * 
 * @author GiftFlow
 * @version 1.0.0
 * 
 * @example
 * // Basic usage
 * <SelectSearch
 *   options={[
 *     { value: '1', label: 'Option 1' },
 *     { value: '2', label: 'Option 2' },
 *     { value: '3', label: 'Option 3' }
 *   ]}
 *   value={selectedValue}
 *   onChange={setSelectedValue}
 *   placeholder="Select an option..."
 * />
 * 
 * @example
 * // With descriptions and advanced features
 * <SelectSearch
 *   options={[
 *     { value: '1', label: 'Campaign 1', description: 'Active campaign' },
 *     { value: '2', label: 'Campaign 2', description: 'Completed campaign' }
 *   ]}
 *   value={selectedValue}
 *   onChange={setSelectedValue}
 *   placeholder="Select a campaign..."
 *   searchPlaceholder="Search campaigns..."
 *   loading={isLoading}
 *   error={errorMessage}
 *   disabled={false}
 *   className="custom-class"
 *   id="campaign-select"
 *   name="campaign_id"
 * />
 * 
 * @example
 * // With async data loading
 * const [options, setOptions] = useState([]);
 * const [loading, setLoading] = useState(false);
 * 
 * useEffect(() => {
 *   setLoading(true);
 *   fetchCampaigns().then(data => {
 *     setOptions(data.map(campaign => ({
 *       value: campaign.id,
 *       label: campaign.title,
 *       description: campaign.status
 *     })));
 *     setLoading(false);
 *   });
 * }, []);
 * 
 * <SelectSearch
 *   options={options}
 *   loading={loading}
 *   value={selectedCampaign}
 *   onChange={setSelectedCampaign}
 * />
 * 
 * @param {Object} props - Component props
 * @param {Array} props.options - Array of option objects
 * @param {string} props.options[].value - Unique value for the option
 * @param {string} props.options[].label - Display text for the option
 * @param {string} [props.options[].description] - Optional description text
 * @param {string} props.value - Currently selected value
 * @param {Function} props.onChange - Callback function when selection changes
 * @param {string} [props.placeholder="Select an option..."] - Placeholder text
 * @param {string} [props.searchPlaceholder="Search..."] - Search input placeholder
 * @param {boolean} [props.disabled=false] - Whether the component is disabled
 * @param {boolean} [props.loading=false] - Whether options are loading
 * @param {string|null} [props.error=null] - Error message to display
 * @param {string} [props.className=""] - Additional CSS class names
 * @param {string} [props.id=""] - HTML id attribute
 * @param {string} [props.name=""] - HTML name attribute for form submission
 * 
 * @returns {JSX.Element} SelectSearch component
 * 
 * @features
 * - Real-time search filtering
 * - Keyboard navigation (Arrow keys, Enter, Escape)
 * - Click outside to close
 * - Clear selection button
 * - Loading states with spinner
 * - Error handling and display
 * - Accessibility support (ARIA attributes)
 * - Responsive design
 * - Custom styling support
 * - Form integration
 * 
 * @keyboard
 * - Arrow Down/Up: Navigate through options
 * - Enter: Select highlighted option
 * - Escape: Close dropdown
 * - Space: Open dropdown (when closed)
 * 
 * @accessibility
 * - ARIA-compliant with proper roles and attributes
 * - Keyboard navigation support
 * - Screen reader friendly
 * - Focus management
 * - High contrast support
 * 
 * @styling
 * The component uses BEM methodology for CSS classes:
 * - .giftflow-select-search (main container)
 * - .giftflow-select-search__trigger (clickable area)
 * - .giftflow-select-search__dropdown (options container)
 * - .giftflow-select-search__option (individual option)
 * 
 * Customize styles by targeting these classes or using the className prop.
 */

import { useState, useEffect, useRef } from 'react';
import { createPortal } from 'react-dom';
import { ChevronDown, Search, X } from 'lucide-react';

export default function SelectSearch({ 
  options = [], 
  value = '', 
  onChange = () => {}, 
  placeholder = 'Select an option...',
  searchPlaceholder = 'Search...',
  disabled = false,
  loading = false,
  error = null,
  className = '',
  id = '',
  name = ''
}) {
  const [isOpen, setIsOpen] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [filteredOptions, setFilteredOptions] = useState(options);
  const [highlightedIndex, setHighlightedIndex] = useState(-1);
  
  const containerRef = useRef(null);
  const searchInputRef = useRef(null);
  const optionRefs = useRef([]);
  const dropdownRef = useRef(null);

  // Position dropdown function
  const positionDropdown = () => {
    if (!containerRef.current || !dropdownRef.current) return;
    
    const containerRect = containerRef.current.getBoundingClientRect();
    const dropdown = dropdownRef.current;
    
    // Reset positioning
    dropdown.style.position = 'fixed';
    dropdown.style.top = '0px';
    dropdown.style.left = '0px';
    dropdown.style.width = `${containerRect.width}px`;
    dropdown.style.zIndex = '9999';
    
    // Force a reflow to get accurate measurements
    dropdown.offsetHeight;
    
    // Get updated measurements after positioning
    const dropdownRect = dropdown.getBoundingClientRect();
    const viewportHeight = window.innerHeight;
    const viewportWidth = window.innerWidth;
    const spaceBelow = viewportHeight - containerRect.bottom;
    const spaceAbove = containerRect.top;
    
    let topPosition = containerRect.bottom + 4; //  + window.scrollY + 4;
    let leftPosition = containerRect.left + window.scrollX;
    
    // Check if dropdown would overflow viewport vertically
    if (spaceBelow < dropdownRect.height && spaceAbove > dropdownRect.height) {
      // Position above the trigger
      topPosition = containerRect.top + window.scrollY - dropdownRect.height - 4;
    } else if (spaceBelow < dropdownRect.height && spaceAbove < dropdownRect.height) {
      // Not enough space above or below, position to fit in viewport
      if (spaceBelow > spaceAbove) {
        topPosition = containerRect.bottom + window.scrollY + 4;
        dropdown.style.maxHeight = `${spaceBelow - 8}px`;
      } else {
        topPosition = containerRect.top + window.scrollY - 4;
        dropdown.style.maxHeight = `${spaceAbove - 8}px`;
      }
    }
    
    // Check if dropdown would overflow viewport horizontally
    if (leftPosition + dropdownRect.width > viewportWidth) {
      leftPosition = viewportWidth - dropdownRect.width - 8;
    }
    
    // Ensure dropdown doesn't go off the left edge
    if (leftPosition < 8) {
      leftPosition = 8;
    }
    
    // Apply final positioning
    dropdown.style.top = `${topPosition}px`;
    dropdown.style.left = `${leftPosition}px`;
  };

  // Filter options based on search term
  useEffect(() => {
    if (!searchTerm.trim()) {
      setFilteredOptions(options);
    } else {
      const filtered = options.filter(option => 
        option.label.toLowerCase().includes(searchTerm.toLowerCase()) ||
        option.value.toLowerCase().includes(searchTerm.toLowerCase())
      );
      setFilteredOptions(filtered);
    }
    setHighlightedIndex(-1);
    
    // Reposition dropdown if it's open and options changed
    if (isOpen) {
      setTimeout(() => {
        positionDropdown();
      }, 0);
    }
  }, [searchTerm, options, isOpen]);

  // Handle click outside to close dropdown
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (containerRef.current && !containerRef.current.contains(event.target) &&
          dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setIsOpen(false);
        setSearchTerm('');
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  // Focus search input when dropdown opens
  useEffect(() => {
    if (isOpen && searchInputRef.current) {
      searchInputRef.current.focus();
    }
  }, [isOpen]);

  // Position dropdown when it opens
  useEffect(() => {
    if (isOpen) {
      // Use setTimeout to ensure DOM is ready
      const timeoutId = setTimeout(() => {
        positionDropdown();
      }, 0);
      
      return () => clearTimeout(timeoutId);
    }
  }, [isOpen]);

  // Reposition on window resize or scroll
  useEffect(() => {
    if (!isOpen) return;
    
    const handleReposition = () => {
      positionDropdown();
    };
    
    window.addEventListener('resize', handleReposition);
    window.addEventListener('scroll', handleReposition, true);
    
    return () => {
      window.removeEventListener('resize', handleReposition);
      window.removeEventListener('scroll', handleReposition, true);
    };
  }, [isOpen]);

  // Cleanup dropdown from body when component unmounts or dropdown closes
  useEffect(() => {
    return () => {
      if (dropdownRef.current && dropdownRef.current.parentNode) {
        dropdownRef.current.parentNode.removeChild(dropdownRef.current);
      }
    };
  }, []);

  // Remove dropdown from body when closed
  useEffect(() => {
    if (!isOpen && dropdownRef.current && dropdownRef.current.parentNode) {
      dropdownRef.current.parentNode.removeChild(dropdownRef.current);
    }
  }, [isOpen]);

  // Handle keyboard navigation
  const handleKeyDown = (e) => {
    if (!isOpen) {
      if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
        e.preventDefault();
        setIsOpen(true);
      }
      return;
    }

    switch (e.key) {
      case 'ArrowDown':
        e.preventDefault();
        setHighlightedIndex(prev => 
          prev < filteredOptions.length - 1 ? prev + 1 : 0
        );
        break;
      case 'ArrowUp':
        e.preventDefault();
        setHighlightedIndex(prev => 
          prev > 0 ? prev - 1 : filteredOptions.length - 1
        );
        break;
      case 'Enter':
        e.preventDefault();
        if (highlightedIndex >= 0 && filteredOptions[highlightedIndex]) {
          handleSelect(filteredOptions[highlightedIndex]);
        }
        break;
      case 'Escape':
        setIsOpen(false);
        setSearchTerm('');
        break;
    }
  };

  const handleSelect = (option) => {
    onChange(option.value);
    setIsOpen(false);
    setSearchTerm('');
    setHighlightedIndex(-1);
  };

  const handleClear = (e) => {
    e.stopPropagation();
    onChange('');
    setSearchTerm('');
  };

  const getSelectedOption = () => {
    return options.find(option => option.value === value);
  };

  const selectedOption = getSelectedOption();

  return (
    <div 
      ref={containerRef}
      className={`giftflow-select-search ${className} ${disabled ? 'giftflow-select-search--disabled' : ''} ${error ? 'giftflow-select-search--error' : ''} ${isOpen ? 'giftflow-select-search--open' : ''}`}
    >
      <div
        className="giftflow-select-search__trigger"
        onClick={() => !disabled && !loading && setIsOpen(!isOpen)}
        onKeyDown={handleKeyDown}
        tabIndex={disabled ? -1 : 0}
        role="combobox"
        aria-expanded={isOpen}
        aria-haspopup="listbox"
        aria-labelledby={id}
      >
        <div className="giftflow-select-search__value">
          {selectedOption ? (
            <span className="giftflow-select-search__value-text" dangerouslySetInnerHTML={{ __html: selectedOption.label }}>
            </span>
          ) : (
            <span className="giftflow-select-search__placeholder" dangerouslySetInnerHTML={{ __html: placeholder }}>
            </span>
          )}
        </div>
        
        <div className="giftflow-select-search__indicators">
          {value && !disabled && (
            <button
              type="button"
              className="giftflow-select-search__clear"
              onClick={handleClear}
              aria-label="Clear selection"
            >
              <X size={16} />
            </button>
          )}
          <div className="giftflow-select-search__arrow">
            <ChevronDown size={16} />
          </div>
        </div>
      </div>

      {isOpen && createPortal(
        <div 
          ref={dropdownRef}
          className="giftflow-select-search__dropdown"
        >
          <div className="giftflow-select-search__search">
            <Search size={16} className="giftflow-select-search__search-icon" />
            <input
              ref={searchInputRef}
              type="text"
              className="giftflow-select-search__search-input"
              placeholder={searchPlaceholder}
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              onKeyDown={handleKeyDown}
            />
          </div>
          
          <div className="giftflow-select-search__options" role="listbox">
            {loading ? (
              <div className="giftflow-select-search__loading">
                <div className="giftflow-select-search__spinner"></div>
                <span>Loading options...</span>
              </div>
            ) : filteredOptions.length > 0 ? (
              filteredOptions.map((option, index) => (
                <div
                  key={option.value}
                  ref={el => optionRefs.current[index] = el}
                  className={`giftflow-select-search__option ${
                    option.value === value ? 'giftflow-select-search__option--selected' : ''
                  } ${
                    index === highlightedIndex ? 'giftflow-select-search__option--highlighted' : ''
                  }`}
                  onClick={() => handleSelect(option)}
                  role="option"
                  aria-selected={option.value === value}
                >
                  <span className="giftflow-select-search__option-label" dangerouslySetInnerHTML={{ __html: option.label }}>
                  </span>
                  {option.description && (
                    <span className="giftflow-select-search__option-description" dangerouslySetInnerHTML={{ __html: option.description }}>
                    </span>
                  )}
                </div>
              ))
            ) : (
              <div className="giftflow-select-search__no-options">
                No options found
              </div>
            )}
          </div>
        </div>,
        document.body
      )}

      {error && (
        <div className="giftflow-select-search__error-message">
          {error}
        </div>
      )}

      {/* Hidden input for form submission */}
      <input
        type="hidden"
        name={name}
        value={value}
        id={id}
      />
    </div>
  );
}
