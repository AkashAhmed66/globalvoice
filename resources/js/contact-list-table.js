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
        url: ajaxUrl,
        type: 'GET',
        data: function (d) {
            // Include CSRF token if necessary
            d._token = $('meta[name="csrf-token"]').attr('content');
        }
    },
    columns: [
        { data: 'id' },
        { data: 'group' }, // Group column
        { data: 'phone' },
        { data: 'status' },
        { data: 'action', orderable: false, searchable: false }
    ],


      columnDefs: columnDefs,

      order: [],
      dom:
        '<"card-header d-flex rounded-0 flex-wrap pb-md-0 pt-0"' +
        '<"me-5 ms-n2"f>' +
        '<"d-flex justify-content-start justify-content-md-end align-items-baseline"<"dt-action-buttons d-flex align-items-start align-items-md-center justify-content-sm-center gap-4"lB>>' +
        '>t' +
        '<"row mx-1"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
      lengthMenu: [10, 20, 50, 70, 100], //for length of menu
      language: {
        sLengthMenu: '_MENU_',
        search: '',
        searchPlaceholder: 'Search',
        info: 'Displaying _START_ to _END_ of _TOTAL_ entries'
      },
      // Buttons with Dropdown
      buttons: [
   
        {
          text: '<i class="ri-add-line ri-16px me-0 me-sm-2 align-baseline"></i><span class="d-none d-sm-inline-block">Add New Record</span>',
          className: 'add-new btn btn-primary waves-effect waves-light',
          attr: {
            'data-bs-toggle': 'offcanvas',
            'data-bs-target': '#offcanvasAddRecord'
          }
        }
      ],
      // For responsive popup
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of ' + data['name'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            var data = $.map(columns, function (col, i) {
              return col.title !== '' // ? Do not show row in modal popup if title is blank (for check box)
                ? '<tr data-dt-row="' +
                    col.rowIndex +
                    '" data-dt-column="' +
                    col.columnIndex +
                    '">' +
                    '<td>' +
                    col.title +
                    ':' +
                    '</td> ' +
                    '<td>' +
                    col.data +
                    '</td>' +
                    '</tr>'
                : '';
            }).join('');

            return data ? $('<table class="table"/><tbody />').append(data) : false;
          }
        }
      }
    });
    
  }
});
