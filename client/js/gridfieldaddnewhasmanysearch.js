(function($) {
    $.entwine("ss", function($) {
        /**
         * GridFieldAddhasmanySearchButton
         */

        $(".add-new-hasmany-search-dialog").entwine({
            loadDialog: function(deferred) {
                var dialog = this.addClass("loading").children(".ui-dialog-content").empty();

                deferred.done(function(data) {
                    dialog.html(data).parent().removeClass("loading");
                });
            }
        });

        $(".ss-gridfield .add-new-hasmany-search").entwine({
            onclick: function() {
                var dialog = $("<div></div>").appendTo("body").dialog({
                    modal: true,
                    resizable: false,
                    width: 500,
                    height: 600,
                    close: function() {
                        $(this).dialog("destroy").remove();
                    }
                });

                dialog.parent().addClass("add-new-hasmany-search-dialog").loadDialog(
                    $.get(this.prop("href"))
                );
                dialog.data("grid", this.closest(".ss-gridfield"));
                var reloadAttr = $(this).attr('data-reloads');
                if (typeof reloadAttr !== 'undefined' &&  reloadAttr !== null) {
                    dialog.data("gridsToReload", $.parseJSON(reloadAttr));
                }
                return false;
            }
        });

        $(".add-new-hasmany-search-dialog .add-new-hasmany-search-form").entwine({
            onsubmit: function() {
                this.closest(".add-new-hasmany-search-dialog").loadDialog($.get(
                    this.prop("action"), this.serialize()
                ));
                return false;
            }
        });

        // Allow the list item to be clickable as well as the anchor
        $('.add-new-hasmany-search-dialog .add-new-hasmany-search-items .list-group-item-action').entwine({
            onclick: function() {
                if (this.children('a').length > 0) {
                    this.children('a').first().trigger('click');
                }
            }
        });

        $(".add-new-hasmany-search-dialog .add-new-hasmany-search-items a").entwine({
            onclick: function() {
                var link = this.closest(".add-new-hasmany-search-items").data("add-link");
                var id   = this.data("id");

                var dialog = this.closest(".add-new-hasmany-search-dialog")
                    .addClass("loading")
                    .children(".ui-dialog-content")
                    .empty();

                $.post(link, { id: id }, function(data)
                {
                    if (typeof data !== 'undefined' && data !== null)
                    {
                        var editLink = data.edit;
                        if (typeof editLink !== 'undefined' && editLink !== null) {
                            window.location = editLink;
                            return;
                        }
                    }

                    dialog.data("grid").reload();

                    var gridsToReload = dialog.data('gridsToReload');
                    if (typeof gridsToReload !== 'undefined' && gridsToReload !== null) {
                        $.each(gridsToReload, function(i, gridName) {
                            $('.ss-gridfield[data-name="' + gridName + '"]').reload();
                        });
                    }

                    dialog.dialog("close");
                }, 'json');

                return false;
            }
        });

        $(".add-new-hasmany-search-dialog .add-new-hasmany-search-pagination a").entwine({
            onclick: function() {
                this.closest(".add-new-hasmany-search-dialog").loadDialog($.get(
                    this.prop("href")
                ));
                return false;
            }
        });
    });
})(jQuery);
