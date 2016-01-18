jQuery(function ($) {
    $('#dueDate_filter').datetimepicker({
        timepicker: false,
        format: 'Y-m-d',
        onChangeDateTime: function (date, $e) {
            mQuery.ajax({
                showLoadingBar: true,
                url: $e.data('action') + '?filter=' + $e.val(),
                type: 'GET',
                success: function (response) {
                    Mautic.processPageContent(response);
                    Mautic.stopPageLoadingBar();
                }
            });
        }
    });

    $('#btn-dueDate-filter').click(function (e) {
        $('#dueDate_filter').val('');
        mQuery.ajax({
            showLoadingBar: true,
            url: $(e.currentTarget).data('action') + '?filter=',
            type: 'GET',
            success: function (response) {
                Mautic.processPageContent(response);
                Mautic.stopPageLoadingBar();
            }
        });
    });

    $('#btn-ownerMe-filter').click(function (e) {
        mQuery.ajax({
            showLoadingBar: true,
            url: $(e.currentTarget).data('href'),
            type: 'GET',
            success: function (response) {
                Mautic.processPageContent(response);
                Mautic.stopPageLoadingBar();
            }
        });
    });

    // Disable keyboard shortcuts
    Mousetrap.stopCallback = function (e, element) {
        if (element.className.indexOf('nomousetrap') > -1) {
            return true;
        }

        if (element.tagName == 'INPUT') {
            return true;
        }
    }
});

function taskMakeCompleted(e, link) {
    var count = parseInt(mQuery('#TaskCount').html());
    mQuery('#TaskCount').html(count - 1);
    jQuery(e).closest('li').remove();
    jQuery.get(link);
}

Mautic.taskOnLoad = function (container, response) {
    if (response) {
        if (response.upTaskCount) {
            var count = parseInt(mQuery('#TaskCount').html());
            count = (response.upTaskCount) ? count + 1 : count - 1;

            mQuery('#TaskCount').html(count);
        }

        if (response.html) {
            if (response.taskId) {
                var el = '#Task' + response.taskId;
                if (mQuery(el).length) {
                    mQuery(el).replaceWith(response.html);
                } else {
                    mQuery('#taskTab .tasks').prepend(response.html);
                }

                mQuery(el + " *[data-toggle='ajaxmodal']").off('click.ajaxmodal');
                mQuery(el + " *[data-toggle='ajaxmodal']").on('click.ajaxmodal', function (event) {
                    event.preventDefault();

                    Mautic.ajaxifyModal(this, event);
                });
            }
        }
    }
};