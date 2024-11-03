document.addEventListener('DOMContentLoaded', function() {
    var tooltips = document.querySelectorAll('.tooltip-icon');
    var closeButtons = document.querySelectorAll('.close');

    tooltips.forEach(function(tooltip) {
        tooltip.addEventListener('click', function() {
            var popupClass = this.getAttribute('data-popup');
            var popup = document.querySelector('.' + popupClass);
            popup.style.display = 'block';
        });
    });

    closeButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var popup = this.closest('.popup');
            popup.style.display = 'none';
        });
    });

    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('popup')) {
            event.target.style.display = 'none';
        }
    });
});
