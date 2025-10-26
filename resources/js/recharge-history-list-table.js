'use strict';
$(function () {
  var data_table = $('#datatable');
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  //var columnDefs = [];
  var columnDefs = [{
    // Serial number column definition
    targets: 0, 
    title: '#', // Label for the serial number column
    render: function (data, type, full, meta) {
      // Return the row number (index + 1)
      return meta.row + meta.settings._iDisplayStart + 1;
    },
    orderable: false, // Make the serial number column not sortable
  }];
  Object.entries(tableHeaders).forEach(function([key, value], index) {
    var columnDef = {
      targets: index,
      sortable: true,
      render: function(data, type, full, meta) {
        return `<span>${full[key]}</span>`;
      }
    };
    columnDefs.push(columnDef);
  });

  if (data_table.length) {
    var dt_user = data_table.DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: ajaxUrl
      },
     columns: [ {data: ''}, Object.entries(tableHeaders).map(([key, value]) => {
        return { data: key };
      })],

      columnDefs: columnDefs,
      lengthMenu: [10, 20, 50, 70, 100], //for length of menu
      language: {
        sLengthMenu: '_MENU_',
        search: '',
        searchPlaceholder: 'Search',
        info: 'Displaying _START_ to _END_ of _TOTAL_ entries'
      },
    });
    
  }
});
