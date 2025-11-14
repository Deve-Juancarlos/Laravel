
document.addEventListener('DOMContentLoaded', function () {

    const sidebarToggle = document.getElementById('sidebar-toggle');
    const mainWrapper = document.querySelector('.main-wrapper');
    const body = document.body;

    const SIDEBAR_STATE_KEY = 'sidebar.collapsed.desktop';
    const isMobile = window.innerWidth <= 768;

  
    function applySidebarState() {
        if (!isMobile) { 
            const isCollapsed = localStorage.getItem(SIDEBAR_STATE_KEY);
            if (isCollapsed === 'true') {
                body.classList.add('sidebar-collapsed');
            }
        }
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            
            if (window.innerWidth <= 768) {
              
                body.classList.toggle('sidebar-mobile-active');

            } else {
                
                body.classList.toggle('sidebar-collapsed');
                
                
                const isNowCollapsed = body.classList.contains('sidebar-collapsed');
                localStorage.setItem(SIDEBAR_STATE_KEY, isNowCollapsed);
            }
        });
    }

    applySidebarState();
    
    var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });

});
