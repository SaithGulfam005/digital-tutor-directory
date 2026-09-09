(function () {
  const salesCtx = document.getElementById('salesChart');
  const growthCtx = document.getElementById('growthChart');
  const chartData = window.adminChartData || {};
  if (!salesCtx && !growthCtx) return;

  if (salesCtx) {
    new Chart(salesCtx, {
      type: 'line',
      data: {
        labels: chartData.revenueLabels || [],
        datasets: [{
          label: 'Revenue (PKR)',
          data: chartData.revenue || [],
          borderColor: '#2563EB',
          backgroundColor: 'rgba(37,99,235,0.1)',
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function (context) {
                const value = Number(context.parsed.y || 0);
                return 'Revenue: PKR ' + value.toLocaleString('en-PK');
              }
            }
          }
        },
        scales: {
          y: {
            ticks: {
              callback: function (value) {
                return 'PKR ' + Number(value).toLocaleString('en-PK');
              }
            }
          }
        }
      }
    });
  }

  if (growthCtx) {
    new Chart(growthCtx, {
      type: 'bar',
      data: {
        labels: chartData.userLabels || [],
        datasets: [{
          label: 'Users',
          data: chartData.users || [],
          backgroundColor: '#2563EB'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            display: true,
            position: 'top',
            labels: {
              boxWidth: 18,
              usePointStyle: true,
              pointStyle: 'rect'
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0
            }
          }
        }
      }
    });
  }
})();