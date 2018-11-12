$(document)
    .on('click', 'a[href][data-lightbox]', function() {
        var gallery = $(this).data().lightbox,
            elements = $(this),
            images = [],
            index = 0;

        if (gallery) {
            elements = $('a[href][data-lightbox="' + gallery + '"]');
            index = elements.index(this);
        }

        elements.each(function() {
            images.push({
                src: $(this).attr('href'),
                title: $(this).data().title || $(this).attr('title')
            });
        });

        STUDIP.Lightbox.setImages(images);
        STUDIP.Lightbox.show(index);

        return false;
    })
    .on('resize', function() {
        STUDIP.Lightbox.init();
    })
    .ready(function() {
        STUDIP.Lightbox.init();
    });
