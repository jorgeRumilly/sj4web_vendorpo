/**
 * SJ4WEB Vendor PO - Supplier Summary Toggle
 * Handles collapsible shipment details
 */

document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.querySelector('.toggle-shipments-detail');
    const detailsContainer = document.querySelector('.packages-details');

    if (toggleBtn && detailsContainer) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();

            const action = this.getAttribute('data-action');
            const icon = this.querySelector('.material-icons');
            const text = this.querySelector('.shippingexpand_text');

            if (action === 'show') {
                // Show details
                detailsContainer.classList.add('show');
                this.setAttribute('data-action', 'hide');
                icon.textContent = 'expand_less';
                text.textContent = text.getAttribute('data-hide-text');
                this.classList.add('active');
            } else {
                // Hide details
                detailsContainer.classList.remove('show');
                this.setAttribute('data-action', 'show');
                icon.textContent = 'expand_more';
                text.textContent = text.getAttribute('data-show-text');
                this.classList.remove('active');
            }
        });
    }
});