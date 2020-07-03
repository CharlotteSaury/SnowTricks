$(function () {

    var tricksPerPage = 5;
    var tricks = $('div.trick-card-div');
    $('#arrowUp').hide();
    $('#loadLessBtn').hide();

    for (var i = tricksPerPage; i <= tricks.length - 1; i++) {
        tricks[i].remove();
    }

    $('#loadMoreBtn').on('click', function (e) {
        e.preventDefault();
        tricksPerPage += 5;
        for (var i = 0; i <= tricksPerPage -1; i++) {
            $('#trickList').append(tricks[i]);
        }
        if (tricks.length <= tricksPerPage) {
            $('#loadLessBtn').show();
            $('#loadMoreBtn').hide();
        }
        if (tricksPerPage >= 15) {
            $('#arrowUp').show();
        }
    });

    $('#loadLessBtn').on('click', function (e) {
        e.preventDefault();
        tricksPerPage = 5;
        for (var i = tricksPerPage; i <= tricks.length - 1; i++) {
            tricks[i].remove();
        }
        $('#loadLessBtn').hide();
        $('#loadMoreBtn').show();
        $('#arrowUp').hide();
        
    });
});