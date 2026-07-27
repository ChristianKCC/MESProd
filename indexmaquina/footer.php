<script type="text/javascript" src="../assets/jquery.min.js"></script>
<script type="text/javascript" src="../assets/sweetalert2.all.min.js"></script>
<script type="text/javascript" src="../assets/axios.min.js"></script>
<script type="text/javascript" src="../assets/bootstrap5/js/bootstrap.min.js"></script>
<script src="../assets/chartjs/Chart.min.js"></script>
<script type="text/javascript">
    function showSection(sectionId) {
        const sections = document.querySelectorAll('.section');
        sections.forEach(section => {
            section.classList.remove('active');
        });

        const selectedSection = document.getElementById(sectionId);
        if (selectedSection) {
            selectedSection.classList.add('active');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        showSection('sectionplaticas');
    });
</script>