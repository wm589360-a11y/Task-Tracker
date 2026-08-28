/**
 * Portfolio Management Real-Time Engine
 */
document.addEventListener('DOMContentLoaded', () => {
    let lastTimestamp = null;
    let syncInterval = null;
    const SYNC_FREQUENCY = 4000; // Poll every 4 seconds

    const liveStatusBadge = document.getElementById('liveSyncBadge');
    const liveStatusText = document.getElementById('liveSyncText');

    function setSyncStatus(state, message) {
        if (!liveStatusBadge) return;
        if (state === 'active') {
            liveStatusBadge.className = 'badge bg-success-subtle text-success border border-success d-inline-flex align-items-center gap-1';
            liveStatusBadge.innerHTML = '<span class="spinner-grow spinner-grow-sm text-success" role="status" style="width: 0.5rem; height: 0.5rem;"></span> ' + message;
        } else if (state === 'syncing') {
            liveStatusBadge.className = 'badge bg-warning-subtle text-warning border border-warning d-inline-flex align-items-center gap-1';
            liveStatusBadge.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> ' + message;
        } else {
            liveStatusBadge.className = 'badge bg-danger-subtle text-danger border border-danger d-inline-flex align-items-center gap-1';
            liveStatusBadge.innerHTML = '<i class="bi bi-wifi-off"></i> ' + message;
        }
    }

    function animateValueChange(element, newValue) {
        if (!element) return;
        const currentText = element.textContent.trim();
        if (currentText !== String(newValue)) {
            element.textContent = newValue;
            element.classList.add('pulse-highlight');
            setTimeout(() => element.classList.remove('pulse-highlight'), 1000);
        }
    }

    async function fetchRealtimeUpdates() {
        try {
            setSyncStatus('syncing', 'Syncing...');
            let url = APP_URL + '/api/portfolio-stream';
            if (lastTimestamp) {
                url += '?since=' + encodeURIComponent(lastTimestamp);
            }

            const response = await fetch(url);
            if (!response.ok) throw new Error('HTTP error ' + response.status);

            const data = await response.json();
            if (data.success) {
                lastTimestamp = data.timestamp;
                setSyncStatus('active', 'Live Sync Active');

                if (data.summary) {
                    updateSummaryMetrics(data.summary);
                }

                if (data.projects) {
                    updateProjectGrid(data.projects);
                }
            }
        } catch (err) {
            console.warn('Real-time portfolio sync error:', err);
            setSyncStatus('error', 'Offline');
        }
    }

    function updateSummaryMetrics(summary) {
        animateValueChange(document.getElementById('metricTotalProjects'), summary.total_projects || 0);
        animateValueChange(document.getElementById('metricActiveProjects'), summary.active_projects || 0);
        animateValueChange(document.getElementById('metricOnTrack'), summary.on_track_health || 0);
        animateValueChange(document.getElementById('metricAtRisk'), summary.at_risk_health || 0);
        animateValueChange(document.getElementById('metricOffTrack'), summary.off_track_health || 0);
        
        const formattedBudget = '$' + (parseFloat(summary.total_budget || 0)).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        animateValueChange(document.getElementById('metricTotalBudget'), formattedBudget);
        
        const formattedSpent = '$' + (parseFloat(summary.total_spent || 0)).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        animateValueChange(document.getElementById('metricTotalSpent'), formattedSpent);

        const burnEl = document.getElementById('metricOverallBurn');
        if (burnEl) {
            animateValueChange(burnEl, (summary.overall_burn || 0) + '%');
        }

        const taskCompEl = document.getElementById('metricOverallTaskComp');
        if (taskCompEl) {
            animateValueChange(taskCompEl, (summary.overall_progress || 0) + '%');
        }
    }

    function updateProjectGrid(projects) {
        projects.forEach(prj => {
            // Update Progress Bar & Percentage
            const progressBar = document.getElementById(`prj-progress-bar-${prj.id}`);
            const progressText = document.getElementById(`prj-progress-text-${prj.id}`);
            if (progressBar && progressText) {
                progressBar.style.width = prj.progress + '%';
                progressBar.setAttribute('aria-valuenow', prj.progress);
                progressText.textContent = prj.progress + '%';
            }

            // Update Budget Burn Bar
            const budgetBar = document.getElementById(`prj-budget-bar-${prj.id}`);
            const budgetBurnText = document.getElementById(`prj-budget-burn-${prj.id}`);
            if (budgetBar && budgetBurnText) {
                budgetBar.style.width = Math.min(prj.budget_burn, 100) + '%';
                budgetBurnText.textContent = prj.budget_burn + '%';
            }

            // Update Health Badge
            const healthBadge = document.getElementById(`prj-health-badge-${prj.id}`);
            if (healthBadge) {
                let badgeClass = 'bg-success';
                if (prj.health === 'At Risk') badgeClass = 'bg-warning text-dark';
                if (prj.health === 'Off Track') badgeClass = 'bg-danger';
                if (prj.health === 'On Hold') badgeClass = 'bg-secondary';
                
                healthBadge.className = `badge ${badgeClass} dropdown-toggle cursor-pointer`;
                healthBadge.textContent = prj.health;
            }

            // Update Status Badge
            const statusBadge = document.getElementById(`prj-status-badge-${prj.id}`);
            if (statusBadge) {
                let statusClass = 'bg-info text-dark';
                if (prj.status === 'Completed') statusClass = 'bg-success';
                if (prj.status === 'Planning') statusClass = 'bg-secondary';
                if (prj.status === 'On Hold') statusClass = 'bg-warning text-dark';
                
                statusBadge.className = `badge ${statusClass}`;
                statusBadge.textContent = prj.status;
            }
        });
    }

    // Quick Health Update handler
    window.quickUpdateHealth = async function(projectId, newHealth) {
        try {
            const response = await fetch(APP_URL + '/api/portfolio-quick-update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: projectId, health: newHealth })
            });
            const data = await response.json();
            if (data.success) {
                fetchRealtimeUpdates();
            }
        } catch (e) {
            console.error('Failed to update health:', e);
        }
    };

    // Initial fetch and set interval
    fetchRealtimeUpdates();
    syncInterval = setInterval(fetchRealtimeUpdates, SYNC_FREQUENCY);
});
