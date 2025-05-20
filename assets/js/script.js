document.addEventListener('DOMContentLoaded', function() {
    // --- Service Routes Page Specific Logic ---
    const serviceRoutesPageContainer = document.querySelector('.page-content-wrapper #service-routes-content-container'); // More specific selector

    if (serviceRoutesPageContainer) {
        const routeSelector = serviceRoutesPageContainer.querySelector('#route-selector');
        const routeImageEl = serviceRoutesPageContainer.querySelector('#route-image');
        const routeTitleEl = serviceRoutesPageContainer.querySelector('#route-title');
        const routeDescriptionTextEl = serviceRoutesPageContainer.querySelector('#route-description-text');
        const portsOfCallListEl = serviceRoutesPageContainer.querySelector('#ports-of-call-list');

        // Data for service routes - This should ideally be passed from PHP
        // For this example, ensure $service_routes_data is available globally via a script tag
        // in index.php when the service-routes page is loaded.

        // Check if routesDataFromPHP is defined (it would be injected by index.php)
        if (typeof routesDataFromPHP !== 'undefined' && routeSelector && routeImageEl && routeTitleEl && routeDescriptionTextEl && portsOfCallListEl) {
            const routesData = routesDataFromPHP;

            // Populate dropdown
            for (const routeCode in routesData) {
                if (routesData.hasOwnProperty(routeCode)) {
                    const option = document.createElement('option');
                    option.value = routeCode;
                    option.textContent = routesData[routeCode].title || routeCode;
                    routeSelector.appendChild(option);
                }
            }

            // Event listener for dropdown change
            routeSelector.addEventListener('change', function() {
                const selectedRouteCode = this.value;
                if (selectedRouteCode && routesData[selectedRouteCode]) {
                    const data = routesData[selectedRouteCode];
                    routeImageEl.src = data.image;
                    routeImageEl.alt = data.title;
                    routeImageEl.style.display = 'block';

                    routeTitleEl.textContent = data.title;
                    routeDescriptionTextEl.textContent = data.description;

                    portsOfCallListEl.innerHTML = ''; // Clear previous ports
                    if (data.ports && data.ports.length > 0) {
                        data.ports.forEach(port => {
                            const li = document.createElement('li');
                            li.textContent = port;
                            portsOfCallListEl.appendChild(li);
                        });
                    } else {
                        const li = document.createElement('li');
                        li.textContent = 'N/A';
                        portsOfCallListEl.appendChild(li);
                    }
                } else {
                    routeImageEl.src = "assets/images/service_routes_maps/placeholder_map.png"; // Reset to placeholder
                    routeImageEl.alt = "Select a route";
                    // routeImageEl.style.display = 'none'; // Or keep placeholder visible
                    routeTitleEl.textContent = 'Route Details';
                    routeDescriptionTextEl.textContent = 'Select a route from the dropdown to view its details.';
                    portsOfCallListEl.innerHTML = '<li>Select a route</li>';
                }
            });

            // Trigger change for initial load if dropdown has options
            if (routeSelector.options.length > 1 && routeSelector.value) { // Ensure a value is selected
                 routeSelector.dispatchEvent(new Event('change'));
            } else if (routeSelector.options.length > 1) {
                routeSelector.value = routeSelector.options[1].value; // Select first actual route
                routeSelector.dispatchEvent(new Event('change'));
            }

        } else {
            if (!serviceRoutesPageContainer) console.log("Service routes container not found");
            else if (typeof routesDataFromPHP === 'undefined') console.log("routesDataFromPHP is not defined. Ensure it's injected in index.php for this page.");
            else console.log("One or more service route display elements are missing.");
        }
    }

    // Add other general site JS here if needed (e.g., mobile menu toggle)
});