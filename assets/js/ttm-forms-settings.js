(function() {

	const toggleSettings = function(module) {
		let id = '';
		if( this.id != null ) {
			module = this;
		}
		if( module.id != null ) {
			id = module.id;
		}

		let els = document.querySelectorAll( "." + id );

		els.forEach(function(el){
			if( module.checked ) {
				el.classList.remove( 'hide' );
				el.classList.add( 'show' );
			}
			else {
				el.classList.remove( 'show' );
				el.classList.add( 'hide' );
			}
		});
	}

	document.addEventListener('DOMContentLoaded', function(){
		let modules = document.querySelectorAll( "[name^='ttm_forms[module-'" );
		modules.forEach(function(module){
			toggleSettings(module);
			module.addEventListener('click',toggleSettings);
		});
	});
})()
