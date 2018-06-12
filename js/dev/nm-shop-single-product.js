(function($) {
	
	'use strict';
	
	// Extend core script
	$.extend($.nmTheme, {
		
		/**
		 *	Initialize single product scripts
		 */
		singleProduct_init: function() {
			var self = this;
            
            self.$galleryContainer       = $('.woocommerce-product-gallery');
            self.$galleryWrap            = self.$galleryContainer.find('.woocommerce-product-gallery__wrapper');
            self.galleryThumbnailsSlider = ( nm_wp_vars.galleryThumbnailsSlider != '0' && $('.nm-single-product').hasClass('thumbnails-vertical')) ? true : false;
            self.galleryData             = self.$galleryContainer.data('flexslider');
            self.zoomEnabled             = (!self.isTouch && $('.woocommerce-product-gallery').hasClass('zoom-enabled'));
            
			
            self.singleProductVariationsInit();
            self.quantityInputsBindButtons($('.summary'));
            self.singleProductGalleryInit();
            self.singleProductGalleryZoomInit();
            self.singleProductFeaturedVideoInit();
			
			
            /* Star-rating: bind click event */
			var $ratingWrap = $('#nm-comment-form-rating');
			$ratingWrap.on('click.nmAddParentClass', '.stars a', function() {
				$ratingWrap.children('.stars').addClass('has-active');
            });
			
			
            /* Related/up-sell products: Load images (init Unveil) */
			var $upsellsImages = $('#nm-upsells').find('.nm-shop-loop-thumbnail .unveil-image'),
				$relatedImages = $('#nm-related').find('.nm-shop-loop-thumbnail .unveil-image'),
				$images = $.merge($upsellsImages, $relatedImages);
			self.$window.load(function() {
				if ($images.length) {
					$images.unveil(1, function() {
						$(this).parents('li').first().addClass('image-loaded');
					});
				}
			});
            
            
            if (nm_wp_vars.shopRedirectScroll != '0') {
                /* Bind: Breadcrumbs (add query arg) */
                $('#nm-breadcrumb').find('a').bind('click.nmShopRedirect', function(e) {
                    e.preventDefault();
                    self.singleProductRedirectWithHash(this);
                });

                /* Bind: Category and tag links */
                $('#nm-product-meta').find('a').bind('click.nmShopRedirect', function(e) {
                    e.preventDefault();
                    self.singleProductRedirectWithHash(this);
                });
            }
		},
        
        
        /**
		 *	Single product: Gallery - Init
		 */
        singleProductGalleryInit: function() {
            // Add "+" icon
            if (nm_wp_vars.galleryZoom != '0') {
                $('.woocommerce-product-gallery').prepend('<a href="#" class="woocommerce-product-gallery__trigger">🔍</a>');
            }
            
            var self = this,
                $galleryImages = self.$galleryWrap.children('.woocommerce-product-gallery__image');
            
            // Is there more than one gallery image?
            if ($galleryImages.length > 1) {
                // Enable thumbnail slider?
                if (self.galleryThumbnailsSlider) {
                    self.$galleryThumbsContainer = self.$galleryContainer.find('.flex-control-thumbs');
                    self.$galleryThumbsContainer.wrapInner('<ol id="nm-product-gallery-thumbs-inner"></ol>');
                    self.$galleryThumbsWrap = $('#nm-product-gallery-thumbs-inner');
                }

                // Set height
                self.$window.load(function() {
                    self.singleProductGallerySetHeight();
                });

                // Flexslider: "before" change slide event
                self.galleryData.vars.before = function(slider) {
                    self.singleProductGallerySetHeight();
                };
                
                // Arrows: Set offset
                self.singleProductGalleryArrowsOffset();
                // Arrows: Show
                $('.flex-direction-nav').addClass('show');
                
                /* Bind: Window "resize" event */
                var timer = null;
                self.$window.resize(function() {
                    if (timer) { clearTimeout(timer); }
                    timer = setTimeout(function() {
                        self.singleProductGallerySetHeight();
                        self.singleProductGalleryArrowsOffset();
                    }, 250);
                });
            }
        },
        
        
        /**
         *	Single product: Gallery - Set height
         */
        singleProductGallerySetHeight: function() {
            var self = this,
                $currentImage = self.galleryData.slides.eq(self.galleryData.animatingTo),
                currentImageHeight = $currentImage.height();

            self.$galleryWrap.css('height', currentImageHeight+'px');

            if (self.galleryThumbnailsSlider) {
                self.singleProductGalleryPositionThumbnail(currentImageHeight);
            }
        },
        
        
        /**
         *	Single product: Gallery - Set thumbnail scroll position
         */
        singleProductGalleryPositionThumbnail: function(currentImageHeight) {
            var self = this;
            
            self.$galleryThumbsContainer.css('height', currentImageHeight+'px');

            var $currentThumb = self.$galleryThumbsWrap.children().eq(self.galleryData.animatingTo),
                thumbPos = Math.round($currentThumb.position().top),
                thumbPosHeight = Math.round(thumbPos + $currentThumb.height()),
                currentPosY = Math.abs(parseInt(self.$galleryThumbsWrap.css('top'))),
                newPosY = null;
            
            // Is thumbnail above the visible viewport?
            if ((thumbPos - currentPosY) <= 50) { // Using 50 as the tolerance
                var $prevThumb = $currentThumb.prev();
                if ($prevThumb.length) {
                    thumbPos = Math.round($prevThumb.position().top);
                }
                newPosY = thumbPos;
            } 
            // Is thumbnail below the visible viewport?
            else if ((thumbPosHeight - currentPosY) >= (currentImageHeight - 50)) { // Using 50 as the tolerance
                var $nextThumb = $currentThumb.next();
                if ($nextThumb.length) {
                    thumbPosHeight = Math.round($nextThumb.position().top + $nextThumb.height());
                }
                newPosY = thumbPosHeight - currentImageHeight;
            }
            
            if (newPosY !== null) {
                self.$galleryThumbsWrap.css('top', '-'+newPosY+'px');
            }
        },
		
        
        /**
		 *	Single product: Redirect to shop with #shop URL hash (scrolls the page to the shop section)
		 */
		singleProductRedirectWithHash: function(shopLink) {
            var url = $(shopLink).attr('href');
            window.location.href = url + '#shop';
        },
		
        
		/**
		 *	Single product: Variations
		 */
		singleProductVariationsInit: function() {
			var self = this;
			
			
			/* Variations: Elements */
			self.$variationsForm = $('#nm-variations-form');
			self.$variationsWrap = self.$variationsForm.children('.variations');
			self.$variationDetailsWrap = self.$variationsForm.children('.single_variation_wrap').children('.single_variation');
            
				
			/* Variations: Select boxes */
            if (self.shopCustomSelect) {
                self.$variationsWrap.find('select').selectOrDie(self.shopSelectConfig);
            }
			
			
			/* Variation details: Init */
            self.shopCheckVariationDetails(self.$variationDetailsWrap);
            
            
            /* Variation details: Bind WooCommerce "show_variation" event */
			self.$variationDetailsWrap.on('show_variation', function() {
                //self.$variationDetailsWrap.css('display', 'block'); // Add "display:block" to skip default animation
                self.shopCheckVariationDetails(self.$variationDetailsWrap);
            });
            
            
            /* Variation details: Bind WooCommerce "hide_variation" event */
			self.$variationDetailsWrap.on('hide_variation', function() {
                self.$variationDetailsWrap.css('display', 'none'); // Add "display:none" to skip default animation
            });
			
			/* Variations select: "woocommerce_variation_select_change" event */
			self.$variationsForm.on('woocommerce_variation_select_change', function() {
                // Gallery zoom: Update image (in case a variation image is used)
				if (self.zoomEnabled) {
					self.singleProductZoomUpdateImage();
				}
			});
		},
        
        
        /**
		 *	Single product: Gallery arrows - Set offset
		 */
        singleProductGalleryArrowsOffset: function() {
            var $galleryContainer = $('.woocommerce-product-gallery'),
                $galleryArrows = $galleryContainer.children('.flex-direction-nav').find('a'),
                
                galleryContainerHeight = Math.ceil($galleryContainer.outerHeight()),
                galleryHeight = Math.ceil($galleryContainer.children('.woocommerce-product-gallery__wrapper').height()),
                
                galleryArrowDefaultOffset = $galleryArrows.first().outerHeight() / 2,
                galleryArrowOffset = (galleryContainerHeight > galleryHeight) ? (galleryContainerHeight - galleryHeight) / 2 : 0;
            
            $galleryArrows.css('marginTop', '-'+(galleryArrowDefaultOffset + galleryArrowOffset)+'px');
        },
        
        
        /**
		 *	Single product: Gallery zoom
		 */
        singleProductGalleryZoomInit: function() {
            var self = this;
            
            // Gallery: Hover zoom (EasyZoom)
            if (self.zoomEnabled) {
                self.$window.load(function() {                  
                    var $productGalleryImages = $('.woocommerce-product-gallery__wrapper').children('.woocommerce-product-gallery__image');
                    $productGalleryImages.easyZoom();
                });
            }
        },
        
        
        /**
		 *	Single product: Gallery zoom - Update image
		 */
		singleProductZoomUpdateImage: function() {
			var self = this,
				$firstGalleryImage = $('.woocommerce-product-gallery__wrapper').children('.woocommerce-product-gallery__image').first(),
				firstGalleryImageUrl = $firstGalleryImage.children('a').attr('href');
            
            if (firstGalleryImageUrl && firstGalleryImageUrl.length > 0) {
                // Get the zoom plugin API for the first gallery image
                var zoomApi = $firstGalleryImage.data('easyZoom');
                // Swap/update zoom image url
                zoomApi.swap(firstGalleryImageUrl);
            }
		},
		
		
		/**
		 *	Single product: Featured video
		 */
		singleProductFeaturedVideoInit: function() {
			var self = this;
			
			self.hasFeaturedVideo = false;
			self.$featuredVideoBtn = $('#nm-featured-video-link');
			
			if (self.$featuredVideoBtn.length) {
				self.hasFeaturedVideo = true;
				
				// Bind: Featured video button
				self.$featuredVideoBtn.bind('click', function(e) {
					e.preventDefault();
					
                    // Modal settings
                    var mfpSettings = {
						mainClass: 'nm-featured-video-popup nm-mfp-fade-in',
						closeMarkup: '<a class="mfp-close nm-font nm-font-close2"></a>',
						removalDelay: 180,
						type: 'iframe',
						closeOnContentClick: true,
						closeBtnInside: false
                    };
                    // Modal settings: YouTube - Disable related videos ("rel=0")
                    if (nm_wp_vars.shopYouTubeRelated == '0') {
                        mfpSettings['iframe'] = {
                            patterns: {
                                youtube: {
                                   src: '//www.youtube.com/embed/%id%?rel=0&autoplay=1'
                                }
                            }
                        };
                    }
                    
					// Open video modal
					self.$featuredVideoBtn.magnificPopup(mfpSettings).magnificPopup('open');
				});
			}
		}
		
	});
	
	// Add extension so it can be called from $.nmThemeExtensions
	$.nmThemeExtensions.singleProduct = $.nmTheme.singleProduct_init;
	
})(jQuery);