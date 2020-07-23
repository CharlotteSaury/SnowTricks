$(function () {

    var tricksPerPage = 5;
    var commentsPerPage = 10;

    // LoadMoreTricks and LoadLessTricks buttons

    var tricks = $('div.trick-card-div');
    $('#arrowUp').hide();
    $('#loadLessTricksBtn').hide();
    if (tricks.length <= tricksPerPage) {
        $('#loadMoreTricksBtn').hide();
    }

    for (var i = tricksPerPage; i <= tricks.length - 1; i++) {
        tricks[i].remove();
    }

    $('#loadMoreTricksBtn').on('click', function (e) {
        e.preventDefault();
        tricksPerPage += 5;
        for (var i = 0; i <= tricksPerPage -1; i++) {
            $('#trickList').append(tricks[i]);
        }
        if (tricks.length <= tricksPerPage) {
            $('#loadLessTricksBtn').show();
            $('#loadMoreTricksBtn').hide();
        }
        if (tricksPerPage >= 15) {
            $('#arrowUp').show();
        }
    });

    $('#loadLessTricksBtn').on('click', function (e) {
        e.preventDefault();
        tricksPerPage = 5;
        for (var i = tricksPerPage; i <= tricks.length - 1; i++) {
            tricks[i].remove();
        }
        $('#loadLessTricksBtn').hide();
        $('#loadMoreTricksBtn').show();
        $('#arrowUp').hide();
        
    });

    // LoadMoreComments button
    var comments = $('div.trick-comment');
    if (comments.length <= commentsPerPage) {
        $('#loadMoreCommentsBtn').hide();
    }

    for (var i = commentsPerPage; i <= comments.length - 1; i++) {
        comments[i].remove();
    }

    $('#loadMoreCommentsBtn').on('click', function (e) {
        e.preventDefault();
        commentsPerPage += 5;
        for (var i = 0; i <= commentsPerPage -1; i++) {
            $('#trickComments').append(comments[i]);
        }
        if (comments.length <= commentsPerPage) {
            $('#loadMoreCommentsBtn').hide();
        }
    });

    // Trick images upload

    $('.add-another-collection-widget').click(function (e) {
        var list = $($(this).attr('data-list-selector'));
        // Try to find the counter of the list or use the length of the list
        var counter = list.data('widget-counter') || list.children().length;

        // grab the prototype template
        var newWidget = list.attr('data-prototype');
        // replace the "__name__" used in the id and name of the prototype
        // with a number that's unique to your emails
        // end name attribute looks like name="contact[emails][2]"
        newWidget = newWidget.replace(/__name__/g, counter);
        // Increase the counter
        counter++;
        // And store it, the length cannot be used if deleting widgets is allowed
        list.data('widget-counter', counter);

        // create a new list element and add it to the list
        var newElem = $(list.attr('data-widget-tags')).html(newWidget);
        newElem.appendTo(list);
    });

});