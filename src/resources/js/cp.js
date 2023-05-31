function initSalesDataTable(id, data) {
    const columnCount = data[0].length - 1;
    $('#' + id).DataTable({
        data: data,
        order: [[columnCount, 'desc']],
        deferRender: true,
        responsive: true,
        language: {
            lengthMenu: '_MENU_ sales displayed',
            search: '',
            info: '_START_-_END_ of _TOTAL_ sales',
            infoEmpty: 'Showing 0 to 0 of 0 sales',
            infoFiltered: '(filtered from _MAX_ total sales)',
            zeroRecords: 'No matching sales found',
        },
    });
    $('#' + id + '_wrapper select').wrap('<div class="select"></div>');
    $('#' + id + '_filter input').addClass('text fullwidth')
        .attr('autocomplete', 'off')
        .attr('placeholder', 'Search')
        .wrap('<div class="flex-grow texticon search icon clearable"></div>');
    $('#' + id + '_wrapper').prepend('<div class="toolbar"></div>');
    $('#' + id + '_length').appendTo('#' + id + '_wrapper .toolbar');
    $('#' + id + '_filter').appendTo('#' + id + '_wrapper .toolbar');
}

function openCustomerSlideout(email) {
    const action = 'plugin-sales/slideout/render?email=' + encodeURIComponent(email);
    const slideout = new Craft.CpScreenSlideout(action);
    slideout.open();
    slideout.$saveBtn.remove();
    slideout.$cancelBtn.text(Craft.t('app', 'Close'));
    slideout.on('load', () => {
        const id = slideout.namespace + '-customer-sales';
        const data = $('input[name="' + slideout.namespace + '[customer-sales-data]"]').val();
        initSalesDataTable(id, JSON.parse(data));

        // Defer removing the focus
        setTimeout(() => {
            document.activeElement.blur();
        }, 0);
    });
}
