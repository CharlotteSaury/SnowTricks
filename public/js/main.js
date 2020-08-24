$(function() {

    /* ****** LoadMore Tricks and LoadLess trick buttons ***** */

    var tricksPerPage = 5;
    var commentsPerPage = 10;

    var tricks = $("div.trick-card-div");
    $("#arrowUp").hide();
    $("#loadLessTricksBtn").hide();
    if (tricks.length <= tricksPerPage) {
        $("#loadMoreTricksBtn").hide();
    }

    for (var i = tricksPerPage; i <= tricks.length - 1; i++) {
        tricks[i].remove();
    }

    $("#loadMoreTricksBtn").on("click", function(e) {
        e.preventDefault();
        tricksPerPage += 5;
        for (var i = 0; i <= tricksPerPage - 1; i++) {
            $("#trickList").append(tricks[i]);
        }
        if (tricks.length <= tricksPerPage) {
            $("#loadLessTricksBtn").show();
            $("#loadMoreTricksBtn").hide();
        }
        if (tricksPerPage >= 15) {
            $("#arrowUp").show();
        }
    });

    $("#loadLessTricksBtn").on("click", function(e) {
        e.preventDefault();
        tricksPerPage = 5;
        for (var i = tricksPerPage; i <= tricks.length - 1; i++) {
            tricks[i].remove();
        }
        $("#loadLessTricksBtn").hide();
        $("#loadMoreTricksBtn").show();
        $("#arrowUp").hide();

    });

    /* ****** LoadMore comments button ***** */

    var comments = $("div.trick-comment");
    if (comments.length <= commentsPerPage) {
        $("#loadMoreCommentsBtn").hide();
    }

    for (var i = commentsPerPage; i <= comments.length - 1; i++) {
        comments[i].remove();
    }

    $("#loadMoreCommentsBtn").on("click", function(e) {
        e.preventDefault();
        commentsPerPage += 5;
        for (var i = 0; i <= commentsPerPage - 1; i++) {
            $("#trickComments").append(comments[i]);
        }
        if (comments.length <= commentsPerPage) {
            $("#loadMoreCommentsBtn").hide();
        }
    });



    /* ********** Responsive ********* */

    if (window.matchMedia("(max-width: 576px)").matches) {

        // Navbar
        $("#mainNav").removeClass("sticky-top").addClass("fixed-bottom");
        $("#mainNavBar").removeClass("ml-auto");
        $(".navbar-brand").remove();
        $("#navHome").html("<i class='fas fa-home'></i>");
        $("#navLogin").html("<i class='fas fa-sign-in-alt'></i>");
        $("#navSignup").remove();
        $("#navLogout").html("<i class='fas fa-sign-out-alt'></i>").removeClass("nav-separator");
        $("#navTricks").html("<i class='fas fa-bookmark'></i>").removeClass("nav-separator");
        $("#navDashboard").html("<i class='fas fa-user'></i>").removeClass("dropdown-item").addClass("nav-item nav-link");
        $("#profileDropdown").replaceWith($("#navDashboard"));
        $(".navbar-nav").css("flex-direction", "row").css("justify-content", "between");
        $(".navbar").css("justify-content", "center");
        $(".navbar-nav a:not('.navbar-nav a:last')").css("margin-right", "4rem");

        // Dashboard navbar
        $("#dashboard-nav").addClass("fixed-bottom");
        $("#dashNavLogout").html("<i class='fas fa-sign-out-alt'></i>");
        $("#dashNavTricks").html("<i class='fas fa-list'></i>");
        $("#dashNavDashboard").html("<i class='fas fa-home'></i>");
        $("#dashNavProfile").html("<i class='fas fa-user'></i>");
        $("#dashNavComments").html("<i class='fas fa-comment-dots'></i>");
        $("#dashNavStats").html("<i class='fas fa-chart-bar'></i>");
        $("#dashNavUsers").html("<i class='fas fa-users'></i>");
        $("#dashNavPass, #dashNavAdd").parent().remove();
        $("#dashboard-top a").html("<i class='fas fa-undo-alt mr-2'></i>");

        // Login, Registration, ForgotPass
        $(".login-form").prepend($(".login-div h1")).css("margin-top", "3rem");

        $("#newTrickImages button").html("<i class='fas fa-plus mr-2'></i><i class='fas fa-images'></i>");
        $("#newTrickVideos button").html("<i class='fas fa-plus mr-2'></i><i class='fas fa-video'></i>");
    }


    /* ******** trick page ****** */

    $("#trickPage #trickMedia button").click(function(e) {
        $("#trickPage #trickMedia .media-slider").css("display", "block");
        $(this).css("display", "none");
    });

    $(".trick-media").click(function() {
        var trickId = $(this).attr("id");
        var carouselId = "carousel" + trickId;
        $(".carousel-item[id =" + carouselId + "]").addClass("active");
    })

    $("#modalGallery").on("hide.bs.modal", function(e) {
        $(".carousel-item").removeClass("active");
    })

    /* ******** new/edit trick page ****** */
    // Trick images upload

    $(document).on("change", ".custom-file-input", function() {
        let fileName = $(this).val().replace(/\\/g, "/").replace(/.*\//, "");
        $(this).parent(".custom-file").find(".custom-file-label").text(fileName);
    });

    function handleDeleteButtons() {
        $("button[data-action='delete']").click(function() {
            var target = $(this).attr("data-target");
            $(target).parent().remove();
            updateCounterImage();
            updateCounterVideo();
        })
    }

    function updateCounterImage() {
        var count = +$("#image-fields-list").children().length;
        $("#image-counter").val(count);
    }

    function updateCounterVideo() {
        var count = +$("#video-fields-list").children().length;
        $("#video-counter").val(count);
    }

    $(".add-another-collection-widget").click(function(e) {
        var list = $($(this).attr("data-list-selector"));
        // Try to find the counter of the list or use the length of the list
        var counter = list.data("widget-counter") || list.children().length;

        // grab the prototype template
        var newWidget = list.attr("data-prototype");
        // replace the "__name__" used in the id and name of the prototype
        // with a number that"s unique to your emails
        // end name attribute looks like name="contact[emails][2]"
        newWidget = newWidget.replace(/__name__/g, counter);
        // Increase the counter
        counter++;
        // And store it, the length cannot be used if deleting widgets is allowed
        list.data("widget-counter", counter);

        // create a new list element and add it to the list
        var newElem = $(list.attr("data-widget-tags")).html(newWidget);
        newElem.appendTo(list);
        handleDeleteButtons();
        updateCounterImage();
        updateCounterVideo();
    });

    $(".edit-mainImg").click(function(e) {
        $(".mainImg-input .custom-file").css("display", "block");
    })

    $(".delete-mainImg").click(function(e) {
        $("#trickMainImg").css("background", "none").css("background-color", "grey");
        $(".mainImg-input").css("display", "block");
    })

    $(".edit-media-button").click(function(e) {
        $(this).parent().parent().find(".edit-media-input").css("display", "block");
    })

    $(".delete-media-button").click(function(e) {
        $(this).parent().parent().remove();
    })

    $("#editPage #trickMedia button").click(function(e) {
        $("#editPage #trickMedia .media-slider").css("display", "block");
        $(this).css("display", "none");
        $("#newMedia").css("display", "block !important");
    })


    /* ********** Passing user infos to modal ********* */

    $("#userModal").on("show.bs.modal", function(e) {
        $(this).find("#userModalName").text($(e.relatedTarget).data("name"));
        $(this).find("#userModalAvatar").attr("src", $(e.relatedTarget).data("avatar"));
        $(this).find("#userModalEmail").text($(e.relatedTarget).data("description"));
    });

    /* ********** Passing trick infos to modal ********* */

    $("#deleteTrickModal").on("show.bs.modal", function(e) {
        $(this).find("#trick_deletion").attr("action", $(e.relatedTarget).data("action"));
        $(this).find("#csrf_deletion").attr("value", $(e.relatedTarget).data("token"));
        $(this).find(".modal-title").text("Trick deletion : " + $(e.relatedTarget).data("name"));
    });

    /* ********** Passing trick infos to main image deletion modal ********* */

    $("#deleteMainImageModal").on("show.bs.modal", function(e) {
        $(this).find("#mainImage_deletion").attr("action", $(e.relatedTarget).data("action"));
        $(this).find("#csrf_deletion").attr("value", $(e.relatedTarget).data("token"));
    });

    /* ********** Passing comment infos to modal ********* */

    $("#deleteCommentModal").on("show.bs.modal", function(e) {
        $(this).find("#comment_deletion").attr("action", $(e.relatedTarget).data("action"));
        $(this).find("#csrf_deletion").attr("value", $(e.relatedTarget).data("token"));
    });

    /* ******** user profile page ****** */

    $("#editAvatarBtn").click(function(e) {
        $(".avatar-input .custom-file").css("display", "block");
        $(this).css("display", "none");
    })


});