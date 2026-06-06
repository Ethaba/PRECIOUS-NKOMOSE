document.addEventListener('DOMContentLoaded', function () {
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
