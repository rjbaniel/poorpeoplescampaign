window.onload = function() {
	jQuery("[href=#section-1]").addClass("current");
	var currentSectionIndex = 0;
	var sectionLinks = jQuery( ".ppc-toc a" );
	var sections = jQuery( ".toc-section" );
	var sectionTops = [];
	sections.each( function ( index ) {
		sectionTops[index] = jQuery( this ).offset().top - 80;
	});

	var toc = jQuery( ".ppc-toc a" );
	toc.each( function ( index ) {
		var link = jQuery( this );
		var linkHref = link.attr( 'href' );
		var linkAnchor = jQuery( linkHref );
		var linkAnchorOffset = linkAnchor.offset().top;
		this.addEventListener( 'click', function( e ) {
			e.preventDefault();
			window.scroll( 0, linkAnchorOffset - 90 );
			jQuery( sectionLinks[currentSectionIndex] ).removeClass( "current" );
			jQuery( this ).addClass( "current" );
			currentSectionIndex = jQuery( this ).attr( "href" ).slice(-1) - 1;
			console.log(currentSectionIndex);
		} );
	});

	jQuery(window).scroll ( function() {
		var currentScroll = jQuery(window).scrollTop();
		if (currentScroll > 70) {
			jQuery( ".ppc-toc" ).removeClass("hidden");
		} else {
			jQuery( ".ppc-toc" ).addClass("hidden");
		}
		var currentSection = jQuery( "a.current" );
		if ( currentScroll > sectionTops[currentSectionIndex + 1] - 80 ) {
			jQuery( sectionLinks[currentSectionIndex] ).removeClass( "current" );
			currentSectionIndex++;
			jQuery( sectionLinks[currentSectionIndex] ).addClass( "current" );
		} else if ( currentScroll < sectionTops[currentSectionIndex] - 150 ) {
			if ( currentSectionIndex > 0 ) {
				jQuery( sectionLinks[currentSectionIndex] ).removeClass( "current" );
				currentSectionIndex--;
				jQuery( sectionLinks[currentSectionIndex] ).addClass( "current" );
			}
		}
	})
};