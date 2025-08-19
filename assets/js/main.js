document.addEventListener('DOMContentLoaded', function () {
    // --- UX ENHANCEMENTS ---
    // Disable text selection
    document.body.style.webkitUserSelect = 'none';
    document.body.style.mozUserSelect = 'none';
    document.body.style.msUserSelect = 'none';
    document.body.style.userSelect = 'none';

    // Disable right-click context menu
    document.addEventListener('contextmenu', function (event) {
        event.preventDefault();
    });

    // Disable zoom
    document.addEventListener('keydown', function (event) {
        if ((event.ctrlKey === true || event.metaKey === true) && (event.which === 61 || event.which === 107 || event.which === 173 || event.which === 109 || event.which === 187 || event.which === 189)) {
            event.preventDefault();
        }
    });
    document.addEventListener('wheel', function (event) {
        if (event.ctrlKey === true) {
            event.preventDefault();
        }
    }, { passive: false });


    // --- GLOBAL HELPERS ---
    window.toggleModal = (modalId, show) => {
        const modal = document.getElementById(modalId);
        if (modal) {
            if (show) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            } else {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }
    };

    window.showLoader = (show) => {
        toggleModal('loader-modal', show);
    };

    /**
     * Corrected Generic AJAX function
     * This version adds the 'X-Requested-With' header to every request,
     * which is essential for the PHP backend to identify it as an AJAX call.
     */
    window.sendRequest = async (url, options = {}) => {
        showLoader(true);

        // Ensure options.headers is an object
        if (!options.headers) {
            options.headers = {};
        }

        // Add the crucial header for PHP to recognize the AJAX call
        // This is the main fix for the "Unexpected token '<'" error.
        options.headers['X-Requested-With'] = 'XMLHttpRequest';

        // If the body is FormData, fetch API sets the Content-Type automatically.
        // For other types like JSON, you might need to set it manually.
        // This setup is fine for the current app which uses FormData.

        try {
            const response = await fetch(url, options);

            // Check if the response is JSON before trying to parse it
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.includes("application/json")) {
                if (!response.ok) {
                    // Try to get more info from a JSON error response
                    const errorData = await response.json();
                    throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                showLoader(false);
                return data;
            } else {
                // If the response is not JSON, it's likely a PHP error page (HTML)
                const errorText = await response.text();
                console.error("Server returned non-JSON response:", errorText);
                throw new Error("Unexpected response from server. Check console for details.");
            }
        } catch (error) {
            console.error('Request failed:', error);
            showLoader(false);
            // Display a more specific error message for easier debugging
            showAlert('error', error.message);
            return { success: false, message: error.message };
        }
    };

    // Alert toast
    window.showAlert = (type, message) => {
        const alertContainer = document.getElementById('alert-container');
        if(!alertContainer) return;

        const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';

        const alertEl = document.createElement('div');
        alertEl.className = `fixed top-5 right-5 ${bgColor} text-white py-2 px-4 rounded-lg shadow-lg flex items-center transition-transform transform translate-x-full z-50`;
        alertEl.innerHTML = `<i class="fas ${icon} mr-2"></i><span>${message}</span>`;

        alertContainer.appendChild(alertEl);

        // Animate in
        setTimeout(() => {
            alertEl.classList.remove('translate-x-full');
        }, 10);

        // Animate out and remove
        setTimeout(() => {
            alertEl.classList.add('translate-x-full');
            setTimeout(() => {
                alertEl.remove();
            }, 500);
        }, 3000);
    };
});