Mautic.opportunityOnLoad = function (container, response) {
    if (response) {
        if (response.upOpportunityCount) {
            var count = parseInt(mQuery('#OpportunitiesCount').html());
            count = (response.upOpportunityCount > 0) ? count + 1 : count - 1;

            mQuery('#OpportunitiesCount').html(count);
        }

        if (response.opportunityHtml && response.opportunityId) {
            var el = '#Opportunity' + response.opportunityId;
            if (mQuery(el).length) {
                mQuery(el).replaceWith(response.opportunityHtml);
            } else {
                mQuery('#opportunities-container .opportunities').prepend(response.opportunityHtml);
            }

            mQuery(el + " *[data-toggle='ajaxmodal']").off('click.ajaxmodal');
            mQuery(el + " *[data-toggle='ajaxmodal']").on('click.ajaxmodal', function (event) {
                event.preventDefault();

                Mautic.ajaxifyModal(this, event);
            });

            mQuery(el + " *[data-toggle='confirmation']").off('click.confirmation');
            mQuery(el + " *[data-toggle='confirmation']").on('click.confirmation', function (event) {
                event.preventDefault();
                MauticVars.ignoreIconSpin = true;
                return Mautic.showConfirmation(this);
            });
        }

        if (response.deleted && response.opportunityId) {
            var el = '#Opportunity' + response.opportunityId;
            if (mQuery(el).length) {
                mQuery(el).remove();
            }
        }
    }
};