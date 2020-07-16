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
});