import React from 'react';
import { Waves, MousePointerClick, Settings, TrendingUp } from 'lucide-react';

const Welcome = () => {

  const create_campaign_url = giftflow_admin.admin_url + 'post-new.php?post_type=campaign';
  const settings_url = giftflow_admin.admin_url + 'admin.php?page=giftflow-settings';

  const features = [
    {
      icon: MousePointerClick,
      title: 'Create and launch new fundraising campaigns',
      description: 'in just a few clicks.'
    },
    
    {
      icon: Settings,
      title: 'Customize plugin settings',
      description: 'to match your organization\'s needs.'
    },
    {
      icon: TrendingUp,
      title: 'Track campaign progress',
      description: 'and donor engagement from your dashboard.'
    }
  ];

  return (
    <div className="giftflow-welcome">
      <div className="giftflow-welcome__header">
        <div className="giftflow-welcome__logo-section">
          {/* <Waves width={48} height={48} color='black' /> */}
          <div className="giftflow-welcome__title-section">
            <h2 className="giftflow-welcome__title">GiftFlow Dashboard</h2>
            <p className="giftflow-welcome__subtitle">Your hub for managing fundraising campaigns and settings.</p>
          </div>
        </div>
      </div>

      <div className="giftflow-welcome__content">
        <div className="giftflow-welcome__left-column">
          <div className="giftflow-welcome__features">
            <h3>Key Features</h3>
            <div className="giftflow-welcome__features-list ">
              {
                features.map((feature, index) => (
                  <div className='giftflow-welcome__features-item' key={index}>
                    <feature.icon width={16} height={16} color='black' />
                    <div><strong>{feature.title}</strong> {feature.description}</div>
                  </div>
                ))
              }
            </div>
          </div>
        </div>

        <div className="giftflow-welcome__right-column">
          <div className="giftflow-welcome__actions">
            <a
              href={ create_campaign_url }
              className="giftflow-welcome__action-btn"
            >
              <span role="img" aria-label="Create">➕</span> Create Campaign
            </a>
            <a
              href={ settings_url }
              className="giftflow-welcome__action-btn"
            >
              <span role="img" aria-label="Settings">⚙️</span> Go to Settings
            </a>
          </div>

          <div className="giftflow-welcome__help">
            <h4>Need Help?</h4>
            <p>Visit our <a href="#" target="_blank" rel="noopener noreferrer">documentation</a> or <a href="#" target="_blank" rel="noopener noreferrer">contact support</a>.</p>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Welcome;
