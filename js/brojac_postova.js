jQuery(document).ready(function($) {
    $('.sortiraj').on('click', function() {
        $('.sortiraj').removeClass('active');
        $(this).addClass('active');
        var $this = $(this);
        var $table = $this.closest('table.pregledi-tablica');
        var $tbody = $table.find('tbody');
        var rows = $tbody.find('tr').get();
        var index = $this.index();
        var sortType = $this.data('sort');
        var ascending = !$this.hasClass('asc');

        rows.sort(function(a, b) {
            var datum = $(a).children('td').eq(index).text();
            var boj_pregleda = $(b).children('td').eq(index).text();

            if (sortType === 'date') {
                return ascending ? new Date(datum) - new Date(boj_pregleda) : new Date(boj_pregleda) - new Date(datum);
            } else {
                return ascending ? parseInt(datum) - parseInt(boj_pregleda) : parseInt(boj_pregleda) - parseInt(datum);
            }
        });

        $this.toggleClass('asc', ascending).toggleClass('desc', !ascending);
        $.each(rows, function(_, row) {
            $tbody.append(row);
        });
    });

    $('#toggle-rows').on('click', function() {
        var $button = $(this);
        var $hiddenRows = $('.pregledi-tablica tbody tr').filter(function(index) {
            return index >= 6;
        });

        if ($hiddenRows.is(':visible')) {
            $hiddenRows.hide();
            $button.text('Prikaži više');
        } else {
            $hiddenRows.show();
            $button.text('Sakrij');
        }
    });
});