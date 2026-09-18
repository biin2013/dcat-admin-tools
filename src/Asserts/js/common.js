Dcat.ready(function () {
    function openLayer(content, title, width, height) {
        parent.layer.open({
            title,
            type: 1,
            shade: [0.7, '#000'],
            shadeClose: true,
            scrollbar: false,
            area: [width, height],
            maxmin: true,
            zIndex: 1100,
            content
        });
    }

    $(document).off('click', '.open-layer').on('click', '.open-layer', function () {
        $.ajax({
            url: $(this).data('url'),
            success: res => openLayer(res, $(this).data('title'), $(this).data('width'), $(this).data('height')),
            error: (xhr, status, err) => openLayer(err)
        });
    });
});
