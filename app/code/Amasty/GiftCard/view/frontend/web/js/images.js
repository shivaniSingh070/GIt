/**
 * Slider & upload images logic
 */

define([
    'uiComponent',
    'jquery',
    'Amasty_Base/vendor/slick/slick.min',
    'mage/translate'
], function(Component, $) {
    'use strict';

    return Component.extend({
        defaults: {
            images: [],
            customImage: false,
            isCustomImageAllowed: false,
            errorMessage: '',
            checkedImageId: null,
            customImageFile: '',
            isCustomImageChecked: false,
            stateActive: '-active',
            stateAmcard: '-amcard',
            customImageUrl: '',
            firstImageIndex: 0,
            imports: {
                preconfiguredValues: '${ "giftCard" }:preconfiguredValues'
            },
            element: {
                carousel: '[data-amcard-js="carousel"]',
                customImageSelector: '[data-amcard-js="custom-image"]',
                sliderImages: '[data-amcard-js="amcard-image"]',
                gallery: '[data-gallery-role="gallery"]',
                amGallery: '[data-role="amasty-gallery"]',
                amcardImage: '[data-amcard-js="card-image"]',
                galleryContainer: '[data-gallery-role="gallery-placeholder"]'
            },
            maxSizeUserImage: 1572864,
            extentionsUserImage: [
                'image/png',
                'image/jpeg',
                'image/gif'
            ],
            incorrectSizeMessage: $.mage.__(
                "The image couldn't be uploaded because " +
                "it exceeds 1,5 Mb, the maximum allowed size for uploads."
            ),
            incorrectTypeMessage: $.mage.__(
                "The image couldn't be uploaded, only files with " +
                "the following extensions are allowed: jpg, gif, png"
            )
        },

        initialize: function () {
            this._super();

            $('body').trigger('processStart');
            this.changeSelectedImage(
                this.images[this.firstImageIndex].src,
                this.images[this.firstImageIndex].id
            );

            return this;
        },

        initObservable: function () {
            this._super().observe([
                'customImage',
                'errorMessage',
                'checkedImageId',
                'customImageFile',
                'isCustomImageChecked'
            ]).observe({ isSingleImage: this.images.length === 1 });

            return this;
        },

        getPreconfiguredImage: function () {
            var img = {};

            if (this.customImageUrl) {
                img.src = this.customImageUrl;

                this.customImage(img);
                this.deleteSelectedImage();
                this.isCustomImageChecked(true);
                this.changeMainImage(img);
            }
        },

        /**
         * Validate image size & type
         *
         * @param {Object} userImage
         */
        validateUserImage: function (userImage) {
            if (userImage.size > this.maxSizeUserImage) return this.incorrectSizeMessage;

            if (this.extentionsUserImage.indexOf(userImage.type) === -1) return this.incorrectTypeMessage;

            return '';
        },

        changeSelectedImage: function (src, id, element) {
            this.checkedImageId(id);
            this.isCustomImageChecked(false);
            this.changeMainImage({src});

            if (element) {
                $(this.element.sliderImages).removeClass(this.stateActive);
                $(element).addClass(this.stateActive);
            }
        },

        /**
         * Change main product image
         *
         * @param {Object} newImage
         */
        changeMainImage: function (newImage) {
            var isFotoramaLoaded = this.useFotorama(newImage.src);

            if (!isFotoramaLoaded) {
                $(this.element.galleryContainer).on('gallery:loaded', function () {
                    this.useFotorama(newImage.src);
                }.bind(this));
            }

            this.changeAmastyMainImage(newImage.src);
        },

        /**
         * Use Fotorama widget
         *
         * @param {String} src
         */
        useFotorama: function (src) {
            var fotorama = $(this.element.gallery).data('fotorama');

            if (fotorama) {
                this.changeFotoramaImage(fotorama, src);

                return true;
            }

            return false;
        },

        /**
         * Change Amasty product main image
         *
         * @param {String} src
         */
        changeAmastyMainImage: function (src) {
            var amastyGallery = $(this.element.amGallery),
                amcardImage = $(this.element.amcardImage),
                container = $(this.element.galleryContainer),
                img;

            if (amastyGallery.length) {
                img = $('<img>', { 'class': 'amcard-image', 'src': src, 'data-amcard-js': 'card-image' });
                amastyGallery.replaceWith(img);
                container.addClass(this.stateAmcard);
            }

            if (amcardImage.length) {
                amcardImage.attr('src', src);
            }

            container.innerHeight(container.innerWidth());
            $('body').trigger('processStop');
        },

        /**
         * Change Fotorama image
         *
         * @param {Object} fotorama
         * @param {String} src
         */
        changeFotoramaImage: function (fotorama, src) {
            var newImage = {
                thumb: src,
                img: src,
                full: src
            };

            fotorama.splice(0, fotorama.data.length, newImage);
            $('body').trigger('processStop');
        },

        /**
         * Upload custom user file
         *
         * @param {Object} file
         */
        uploadImage: function (file) {
            var reader;

            if (!file && !file.size) return;

            this.errorMessage(this.validateUserImage(file));

            if (this.errorMessage()) return;

            this.deleteSelectedImage();

            reader = new FileReader();
            reader.onload = function (event) {
                file.src = event.target.result;
                this.customImage(file);
                this.changeMainImage(file);
            }.bind(this);
            reader.readAsDataURL(file);
            this.isCustomImageChecked(true);
        },

        deleteCustomImage: function () {
            this.customImage('');
            $(this.element.customImageSelector).val('');
        },

        deleteSelectedImage: function () {
            $(this.element.sliderImages).removeClass(this.stateActive);
            this.checkedImageId('');
        },

        useCustomImage: function (e) {
            this.isCustomImageChecked(true);
            this.changeMainImage(this.customImage());
            this.deleteSelectedImage();
        },

        getPreconfiguredValue: function (name) {
            var value = this.preconfiguredValues[name];

            if (value) {
                return value;
            }

            return '';
        },

        getPreconfiguredImageSlide: function (name) {
            var imgId = this.getPreconfiguredValue(name),
                img = {},
                imageElement;

            if (!imgId) {
                return '';
            }

            this.checkedImageId(imgId);
            imageElement = $(this.element.sliderImages + '[data-id="' + imgId +'"]');
            imageElement.addClass(this.stateActive);
            img.src = imageElement.attr('src');

            this.changeMainImage(img);
            this.getPreconfiguredImage();
        },

        initSlick: function () {
            $(this.element.carousel).slick(
                {
                    dots: false,
                    infinite: false,
                    slidesToShow: 4,
                    slidesToScroll: 1,
                    responsive: [{
                        breakpoint: 1300,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 1
                        }
                    }, {
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 1
                        }
                    }, {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 4,
                            slidesToScroll: 1
                        }
                    }, {
                        breakpoint: 425,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 1
                        }
                    }]
                }
            );
        }
    });
});
