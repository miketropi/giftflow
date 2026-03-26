import Welcome from './Welcome';
import OverView from './OverView';

const DashboardView = () => {
  return (
    <div className="giftflow-dashboard-view__shell">
      <Welcome />
      <OverView />
    </div>
  );
};

export default DashboardView;
