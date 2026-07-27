document.querySelectorAll('.dropdown').forEach(function (dropdown) {
dropdown.addEventListener('hidden.bs.dropdown', function () {
    this.querySelectorAll('.dropdown-menu.show').forEach(function (submenu) {
    submenu.classList.remove('show');
    });
});
});


// SEGUNDA VERSION CON DESPLIEGUE CON OVER

document.querySelectorAll('.dropdown-submenu').forEach(function (element) {
  element.addEventListener('mouseenter', function () {
    let submenu = this.querySelector('.dropdown-menu');
    if (submenu) submenu.classList.add('show');
  });
  element.addEventListener('mouseleave', function () {
    let submenu = this.querySelector('.dropdown-menu');
    if (submenu) submenu.classList.remove('show');
  });
});

// Cerrar todos los submenús al cerrar el dropdown principal
document.querySelectorAll('.dropdown').forEach(function (dropdown) {
  dropdown.addEventListener('hidden.bs.dropdown', function () {
    this.querySelectorAll('.dropdown-menu.show').forEach(function (submenu) {
      submenu.classList.remove('show');
    });
  });
});
