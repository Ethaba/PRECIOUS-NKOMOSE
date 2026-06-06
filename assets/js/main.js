document.addEventListener('DOMContentLoaded', function () {
  var themeToggle = document.querySelector('[data-theme-toggle]');

  // Keep the theme button, page attribute, and saved preference in sync.
  function applyTheme(theme) {
    var isDark = theme === 'dark';
    document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');

    if (themeToggle) {
      themeToggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
      themeToggle.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
      themeToggle.innerHTML = isDark
        ? '<i class="fa-solid fa-sun"></i><span>Light</span>'
        : '<i class="fa-solid fa-moon"></i><span>Dark</span>';
    }
  }

  var savedTheme = localStorage.getItem('pn-theme') || 'light';
  applyTheme(savedTheme);

  if (themeToggle) {
    themeToggle.addEventListener('click', function () {
      var nextTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      localStorage.setItem('pn-theme', nextTheme);
      applyTheme(nextTheme);
    });
  }

  var tableElements = document.querySelectorAll('.table-card');
  var sectionItems = document.querySelectorAll('.section-list-item');
  var hiddenSectionInput = document.getElementById('preferredSection');
  var selectedSectionText = document.getElementById('preferredSectionText');

  function clearActive(items, activeClass) {
    items.forEach(function (item) {
      item.classList.remove(activeClass);
    });
  }

  function activateSection(sectionElement) {
    clearActive(sectionItems, 'active');
    sectionElement.classList.add('active');
    var sectionId = sectionElement.dataset.sectionId;
    if (hiddenSectionInput) {
      hiddenSectionInput.value = sectionId;
    }
    if (selectedSectionText) {
      selectedSectionText.value = sectionElement.querySelector('h4')?.textContent || sectionId;
    }
  }

  function activateTable(tableElement) {
    clearActive(tableElements, 'selected');
    clearActive(tableElements, 'show-info');
    tableElement.classList.add('selected', 'show-info');
    var tableId = tableElement.dataset.tableId;
    var tableLabel = tableElement.dataset.tableLabel;
    var capacity = tableElement.dataset.tableCapacity;
    if (hiddenSectionInput) {
      hiddenSectionInput.value = tableId;
    }
    if (selectedSectionText) {
      selectedSectionText.value = tableLabel
        ? tableLabel + ' (' + capacity + ' seats)'
        : tableId;
    }
  }

  tableElements.forEach(function (table) {
    table.addEventListener('click', function () {
      activateTable(table);
    });
    table.addEventListener('mouseenter', function () {
      table.classList.add('show-info');
    });
    table.addEventListener('mouseleave', function () {
      if (!table.classList.contains('selected')) {
        table.classList.remove('show-info');
      }
    });
  });

  sectionItems.forEach(function (item) {
    item.addEventListener('click', function () {
      activateSection(item);
    });
  });
});
