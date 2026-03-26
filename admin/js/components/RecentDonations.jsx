export default function RecentDonations({ donations = [] }) {
  const base = typeof giftflow_admin !== 'undefined' && giftflow_admin.admin_url ? giftflow_admin.admin_url : '';
  if (!donations || donations.length === 0) {
    return (
      <div className="giftflow-recent-donations">
        <div className="giftflow-recent-donations__header">
          <h3 className="giftflow-recent-donations__title">Recent Donations</h3>
        </div>
        <div className="giftflow-recent-donations__empty">
          <div className="giftflow-recent-donations__empty-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
          </div>
          <p className="giftflow-recent-donations__empty-text">No recent donations</p>
          <p className="giftflow-recent-donations__empty-subtext">Donations will appear here once they start coming in</p>
        </div>
      </div>
    );
  }

  return (
    <div className="giftflow-recent-donations">
      <div className="giftflow-recent-donations__header">
        <div className="giftflow-recent-donations__header-left">
          <h3 className="giftflow-recent-donations__title">Recent Donations</h3>
          <span className="giftflow-recent-donations__subtitle">Latest contributions to your campaigns</span>
        </div>
        <div className="giftflow-recent-donations__header-right">
          <span className="giftflow-recent-donations__count">{donations.length}</span>
        </div>
      </div>
      
      <div className="giftflow-recent-donations__grid">
        {donations.map((donation) => (
          <div key={donation.id} className="giftflow-recent-donations__card">
            <div className="giftflow-recent-donations__card-header">
              <div className="giftflow-recent-donations__donor-info">
                <div className="giftflow-recent-donations__donor-avatar">
                  {donation.donor_name.charAt(0).toUpperCase()}
                </div>
                <div className="giftflow-recent-donations__donor-details">
                  <h4 className="giftflow-recent-donations__donor-name">
                    <a 
                      href={ `${base}post.php?post=${donation.donor_id}&action=edit` } 
                      className="giftflow-recent-donations__donor-link"
                      target='_blank'
                      dangerouslySetInnerHTML={{ __html: donation.donor_name }}
                    />
                  </h4>
                  <p className="giftflow-recent-donations__donor-email">{donation.donor_email}</p>
                </div>
              </div>
              <div className="giftflow-recent-donations__amount" 
                   dangerouslySetInnerHTML={{ __html: donation.__amount }} />
            </div>
            
            <div className="giftflow-recent-donations__card-body">
              <div className="giftflow-recent-donations__campaign-info">
                <h5 className="giftflow-recent-donations__campaign-title">
                  <a 
                    href={ `${base}post.php?post=${donation.campaign_id}&action=edit` } 
                    target='_blank'
                    className="giftflow-recent-donations__campaign-link"
                    dangerouslySetInnerHTML={{ __html: donation.campaign_title }}
                  />
                </h5>
              </div>
              
              <div className="giftflow-recent-donations__card-footer">
                <div className="giftflow-recent-donations__status-badge">
                  <span className={`giftflow-recent-donations__status giftflow-recent-donations__status--${donation.status}`}>
                    {donation.status}
                  </span>
                </div>
                <div className="giftflow-recent-donations__meta">
                  <span className="giftflow-recent-donations__date">{donation.date}</span>
                  <span className="giftflow-recent-donations__payment-method">
                    via {donation.payment_method}
                  </span>
                </div>
              </div>
            </div>
          </div>
        ))}
      </div>

    <div className="giftflow-recent-donations__footer">
      <a
        href={`${base}edit.php?post_type=donation`}
        className="button button-small">
        View all donations
      </a>
    </div>
    </div>
  );
}