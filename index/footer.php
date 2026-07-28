</div>
<script type="text/javascript" src="../assets/jquery.min.js"></script>
<script type="text/javascript" src="../assets/sweetalert2.all.min.js"></script>
<script type="text/javascript" src="../assets/bootstrap5/js/bootstrap.min.js"></script>
<script type="text/javascript" src="../assets/chartjs/chart.min.js"></script>
<script type="text/javascript" src="../assets/apexcharts/apexcharts.min.js"></script>
<script type="text/javascript" src="../assets/chartjs/googlechart.js"></script>
<script type="text/javascript" src="../assets/axios.min.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    // make it as accordion for smaller screens
    if (window.innerWidth < 992) {

      // close all inner dropdowns when parent is closed
      document.querySelectorAll('.navbar .dropdown').forEach(function(everydropdown) {
        everydropdown.addEventListener('hidden.bs.dropdown', function() {
          // after dropdown is hidden, then find all submenus
          this.querySelectorAll('.submenu').forEach(function(everysubmenu) {
            // hide every submenu as well
            everysubmenu.style.display = 'none';
          });
        })
      });

      document.querySelectorAll('.dropdown-menu a').forEach(function(element) {
        element.addEventListener('click', function(e) {
          let nextEl = this.nextElementSibling;
          if (nextEl && nextEl.classList.contains('submenu')) {
            // prevent opening link if link needs to open dropdown
            e.preventDefault();
            if (nextEl.style.display == 'block') {
              nextEl.style.display = 'none';
            } else {
              nextEl.style.display = 'block';
            }

          }
        });
      })
    }
  });
</script>