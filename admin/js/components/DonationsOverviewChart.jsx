import { useEffect, useState } from "react";
import { getDashboardStatisticsCharts } from "../ulti/api";
import { Line } from "react-chartjs-2";
import { Ban, ChartColumn } from 'lucide-react'; 
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
} from "chart.js";

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend
);

// Align with GiftFlow admin / WP palette (--gfh-chart-* on .giftflow-dashboard-view)
const CHART_COLOR_DONATIONS = { fill: 'rgba(0, 112, 23, 0.55)', stroke: 'rgb(0, 112, 23)' };
const CHART_COLOR_DONORS = { fill: 'rgba(34, 113, 177, 0.5)', stroke: 'rgb(34, 113, 177)' };
const CHART_TEXT = '#1d2327';
const CHART_TEXT_MUTED = '#646970';

export default function DonationsChart() {
  const [dataChart, setDataChart] = useState({
    labels: [],
    donationsData: [],
    donorsData: [],
  });

  // days
  const [period, setPeriod] = useState('7d');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    // Add 15-minute cache for chart data per period
    const fetchChartData = async () => {
      try {
        setLoading(true);
        setError(null);

        // Cache key based on period
        const cacheKey = `giftflow_chartdata_${period}`;
        const cacheRaw = localStorage.getItem(cacheKey);
        let cache = null;
        if (cacheRaw) {
          try {
            cache = JSON.parse(cacheRaw);
          } catch (e) {
            cache = null;
          }
        }

        const now = Date.now();
        const FIFTEEN_MINUTES = 15 * 60 * 1000;

        if (cache && cache.timestamp && (now - cache.timestamp < FIFTEEN_MINUTES) && cache.data) {
          setDataChart({
            labels: cache.data.labels || [],
            donationsData: cache.data.donationsData || [],
            donorsData: cache.data.donorsData || [],
          });
        } else {
          const response = await getDashboardStatisticsCharts({ period: period });
          // Fix: API returns nested object, extract the actual chart data
          const chartData = response.donations_overview_chart_by_period || response;

          setDataChart({
            labels: chartData.labels || [],
            donationsData: chartData.donationsData || [],
            donorsData: chartData.donorsData || [],
          });

          // Save to cache
          localStorage.setItem(
            cacheKey,
            JSON.stringify({
              timestamp: now,
              data: {
                labels: chartData.labels || [],
                donationsData: chartData.donationsData || [],
                donorsData: chartData.donorsData || [],
              }
            })
          );
        }
      } catch (err) {
        console.error('Error fetching chart data:', err);
        setError(err.message || 'Failed to load chart data');
      } finally {
        setLoading(false);
      }
    };

    fetchChartData();
  }, [period]);

  // Show loading state
  if (loading) {
    return (
      <div className="giftflow-chart-loading __monospace">
        <div className="giftflow-chart-loading__spinner"></div>
        <p>Loading chart data...</p>
      </div>
    );
  }

  // Show error state
  if (error) {
    return (
      <div className="giftflow-chart-error __monospace">
        <div className="giftflow-chart-error__icon">
          <Ban size={28} strokeWidth={2} aria-hidden="true" />
        </div>
        <h4>Chart Error</h4>
        <p>{error}</p>
        <button 
          onClick={() => window.location.reload()} 
          className="giftflow-chart-error__retry"
        >
          Retry
        </button>
      </div>
    );
  }

  // Show empty state if no data
  if (
    !dataChart.labels.length ||
    (
      (!dataChart.donationsData || Object.keys(dataChart.donationsData).length === 0) &&
      (!dataChart.donorsData || Object.keys(dataChart.donorsData).length === 0)
    )
  ) {
    return (
      <div className="giftflow-chart-empty __monospace">
        <div className="giftflow-chart-empty__icon">
          <ChartColumn size={28} strokeWidth={2} aria-hidden="true" />
        </div>
        <h4>No Data Available</h4>
        <p>No donation data found for the selected period. Try selecting a different time range or check back later.</p>
      </div>
    );
  }

  const data = {
    labels: dataChart.labels,
    datasets: [
      {
        type: "bar",
        label: "Donation Amount",
        data: dataChart.donationsData,
        backgroundColor: CHART_COLOR_DONATIONS.fill,
        borderColor: CHART_COLOR_DONATIONS.stroke,
        borderWidth: 1,
        yAxisID: "y",
        order: 1,
      },
      {
        type: "bar",
        label: "Donors Registered",
        data: dataChart.donorsData,
        backgroundColor: CHART_COLOR_DONORS.fill,
        borderColor: CHART_COLOR_DONORS.stroke,
        borderWidth: 1,
        yAxisID: "y1",
        order: 2,
      },
    ],
  };

  const options = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
      mode: "index",
      intersect: false,
    },
    plugins: {
      title: {
        display: true,
        text: `Donations & Donors Overview (Last ${period})`,
        font: {
          size: 16,
          weight: '600'
        },
        color: CHART_TEXT
      },
      legend: {
        display: true,
        position: 'top',
        labels: {
          usePointStyle: true,
          padding: 20,
          font: {
            size: 12
          }
        }
      },
      tooltip: {
        backgroundColor: 'rgba(29, 35, 39, 0.92)',
        titleColor: '#fff',
        bodyColor: '#f0f0f1',
        borderColor: '#50575e',
        borderWidth: 1,
        cornerRadius: 6,
        displayColors: true,
        callbacks: {
          label: function(context) {
            if (context.datasetIndex === 0) {
              return `Donations: $${context.parsed.y.toFixed(2)}`;
            } else {
              return `Donors: ${context.parsed.y}`;
            }
          }
        }
      }
    },
    scales: {
      x: {
        display: true,
        title: {
          display: true,
          text: 'Date',
          color: CHART_TEXT_MUTED,
          font: {
            size: 12,
            weight: 'bold'
          }
        },
        grid: {
          display: false
        },
        ticks: {
          color: CHART_TEXT_MUTED,
          maxRotation: 45,
          minRotation: 0
        }
      },
      y: {
        type: "linear",
        display: true,
        position: "left",
        title: {
          display: true,
          text: `Donation Amount (${ giftflow_admin.currency_symbol })`,
          color: CHART_TEXT_MUTED,
          font: {
            size: 12,
            weight: 'bold'
          }
        },
        grid: {
          color: 'rgba(100, 105, 112, 0.2)',
          drawBorder: false
        },
        ticks: {
          color: CHART_TEXT_MUTED,
          callback: function(value) {
            return giftflow_admin.currency_symbol + value.toFixed(0);
          }
        }
      },
      y1: {
        type: "linear",
        display: true,
        position: "right",
        title: {
          display: true,
          text: "Number of Donors",
          color: CHART_TEXT_MUTED,
          font: {
            size: 12,
            weight: 'bold'
          }
        },
        grid: {
          drawOnChartArea: false,
          drawBorder: false
        },
        ticks: {
          color: CHART_TEXT_MUTED,
          stepSize: 1
        }
      },
    },
  };

  return (
    <div className="giftflow-chart-container">
      <div className="giftflow-chart-wrapper">
        <Line data={data} options={options} />
      </div>
      <div className="giftflow-chart-description ">
        <p>
          <strong>Chart Description:</strong> This chart shows donation activity and donor registrations over the <select
            className="giftflow-chart-description__select"
            value={period}
            onChange={e => setPeriod(e.target.value)}
          >
            <option value="7d">last 7 days</option>
            <option value="30d">last 30 days</option>
            <option value="6m">last 6 months</option>
          </select> period.
          <br />
          
        </p>
        <p>
          <span className="giftflow-chart-description__legend giftflow-chart-description__legend--donations">Green bars</span>{' '}
            represent total donation amounts (<b>only completed donations</b>), while the{' '}
            <span className="giftflow-chart-description__legend giftflow-chart-description__legend--donors">blue bars</span>{' '}
            show the number of new donors registered each day.
        </p>
      <div className="giftflow-chart-cache-note">
        <span>
          <strong>Note:</strong> Chart data is cached for 15 minutes to improve performance.
        </span>
        <button
          type="button"
          className="giftflow-chart-clear-cache-btn"
          onClick={() => {
            // Remove cache for all periods
            ['7d', '30d', '6m', '1y'].forEach(periodKey => {
              localStorage.removeItem(`giftflow_chartdata_${periodKey}`);
            });
            // Reload to fetch fresh data
            window.location.reload();
          }}
          title="Clear cached chart data and reload"
        >
          Clear Cache &amp; Reload
        </button>
      </div>
      </div>
    </div>
  );
}
