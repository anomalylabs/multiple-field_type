$(function () {

    // Initialize multiple pickers
    $('[data-provides="anomaly.field_type.multiple"]').each(function () {

        var input = $(this);
        var field = input.data('field_name');
        var wrapper = input.closest('.form-group');
        var modal = $('#' + field + '-modal');

        if (!$('[name="' + field + '"]').length) {
            return;
        }
        
        var selected = $('[name="' + field + '"]').val().split(',');

        modal.on('click', '[data-entry]', function (e) {

            e.preventDefault();

            selected.push(String($(this).data('entry')));

            $('[name="' + field + '"]').val(selected.join(','));

            $(this).closest('tr').addClass('success').fadeOut();

            wrapper.find('.selected').load(APPLICATION_URL + '/streams/multiple-field_type/selected/' + $(this).data('key') + '?uploaded=' + selected.join(','), function () {
                var adjustment;

                var tree = $('.multiple-field_type .selected ul.tree.sortable').sortable({
                    handle: '.handle',
                    nested: false,
                    onDragStart: function ($item, container, _super, event) {
                        $item.css({
                            height: $item.outerHeight(),
                            width: $item.outerWidth()
                        });

                        $item.addClass('dragged');

                        $('body').addClass('dragging');

                        adjustment = {
                            left: container.rootGroup.pointer.left - $item.offset().left,
                            top: container.rootGroup.pointer.top - $item.offset().top
                        };

                        _super($item, container);
                    },
                    onDrag: function ($item, position) {
                        $item.css({
                            left: position.left - adjustment.left,
                            top: position.top - adjustment.top
                        });
                    },
                    onDrop: function ($item, container, _super, event) {

                        selected = [];

                        $.each(tree.sortable('serialize').get()[0], function (key, item) {
                            selected.push(String(item.id));
                        });

                        $('[name="' + field + '"]').val(selected.join(','));

                        _super($item, container);
                    },
                    serialize: function ($parent, $children, parentIsContainer) {

                        var result = $.extend({}, $parent.data());

                        if (parentIsContainer)
                            return [$children];
                        else if ($children[0]) {
                            result.children = $children[0]; // This needs to return [0] for some reason..
                        }

                        delete result.subContainers;
                        delete result.sortable;

                        return result
                    }
                });
            });

            $(wrapper).find('[data-dismiss="multiple"]').removeClass('hidden');
        });

        $(wrapper).on('click', '[data-dismiss="multiple"]', function (e) {

            e.preventDefault();

            selected.splice(selected.indexOf(String($(this).data('entry'))), 1);

            $('[name="' + field + '"]').val(selected.join(','));

            $(this).closest('tr').addClass('danger').fadeOut();
        });

        wrapper.find('.selected table').sortable({
            handle: '.handle',
            itemSelector: 'tr',
            itemPath: '> tbody',
            containerSelector: 'table',
            placeholder: '<tr class="placeholder"/>',
            afterMove: function ($placeholder) {

                $placeholder.closest('table').find('button.reorder').removeClass('disabled');

                $placeholder.closest('table').find('.dragged').detach().insertBefore($placeholder);
            }
        });
    });
});
