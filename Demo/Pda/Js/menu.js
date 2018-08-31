$(function () {

    $(".main_menu").click(function () {
        var url = url_self+'?c=Index&m=main';
        openUrl(url);
    });
    $(".put_in").click(function () {
        var url = url_self+'?c=Stock&m=putInStock';
        openUrl(url);

    });
    $(".update_location").click(function () {
        var url = url_self+'?c=Stock&m=updateLocation';
        openUrl(url);
    });
    $(".stock_count").click(function () {
        var url = url_self+'?c=Stock&m=stockCount';
        openUrl(url);
    });
    $(".query_stock").click(function () {
        var url = url_self+'?c=Stock&m=queryStock';
        openUrl(url);
    });
    $(".change_store").click(function () {
        var url = url_self+'?c=Index&m=index';
        openUrl(url);
    });

});