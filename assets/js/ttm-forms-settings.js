(function() {

	const getParentByClassName = function(node, classname) {
		var parent;
		if (node === null || classname === '') return;
		parent  = node.parentNode;

		while (parent.tagName !== "HTML") {
			if (parent.classList.contains(classname)) {
				return parent;
			}
			parent = parent.parentNode;
		}

		return parent;
	}

	const getParentByTagName = function(node, tagname) {
		var parent;
		if (node === null || tagname === '') return;
		parent  = node.parentNode;
		tagname = tagname.toUpperCase();

		while (parent.tagName !== "HTML") {
			if (parent.tagName === tagname) {
				return parent;
			}
			parent = parent.parentNode;
		}

		return parent;
	}

	const createModal = function(el) {

		const modal = document.createElement("div");
		modal.classList.add('modal');
		modal.classList.add('show');

		const modalDialog = document.createElement("div");
		modalDialog.classList.add('modal-dialog');

		const modalContent = document.createElement("div");
		modalContent.classList.add('modal-content');

		const modalHeader = document.createElement("div");
		modalHeader.classList.add('modal-header');

		const modalTitle = document.createElement("h2");
		modalTitle.classList.add('modal-title');
		modalTitle.innerHTML = el.innerHTML;
		modalHeader.append(modalTitle);


		const modalBody = document.createElement("div");
		modalBody.classList.add('modal-body');

		const modalForm = document.createElement("form");
		modalForm.classList.add('modal-form');

		let entries = JSON.parse(el.dataset.entry);

		for (var prop in entries) {

			const inputDiv = document.createElement("div");
			inputDiv.classList.add('modal-value')

			const inputLabel = document.createElement("label");
			inputLabel.classList.add('modal-label');
			inputLabel.setAttribute('for',prop);
			inputLabel.innerHTML = prop;

			const input = document.createElement("input");
			input.classList.add('modal-input');
			input.setAttribute('id',prop);
			input.value = entries[prop];

			const removeButton = document.createElement("button");
			removeButton.addEventListener('click', function(e){
				e.preventDefault();
				let toRemove = getParentByClassName(removeButton,'modal-value');
				toRemove.remove();
			});
			removeButton.classList.add('button');
			removeButton.classList.add('button-secondary');
			removeButton.innerHTML = '-';

			inputDiv.append(inputLabel);
			inputDiv.append(input);
			inputDiv.append(removeButton);

			modalForm.append(inputDiv);
		}

		const inputAddButton = document.createElement("button");
		inputAddButton.addEventListener('click', function(e){
			e.preventDefault();

			const inputDiv = document.createElement("div");
			inputDiv.classList.add('modal-value')

			const inputLabel = document.createElement("input");
			inputLabel.classList.add('modal-label');

			const input = document.createElement("input");
			input.classList.add('modal-input');

			inputLabel.addEventListener('input',function(){
				input.id = this.value;
			});

			inputDiv.append(inputLabel);
			inputDiv.append(input);

			modalForm.append(inputDiv);
		});
		inputAddButton.classList.add('button');
		inputAddButton.classList.add('button-secondary');
		inputAddButton.innerHTML = 'Add';

		modalBody.append(modalForm);
		modalBody.append(inputAddButton);

		const modalFooter = document.createElement("div");
		modalFooter.classList.add('modal-footer');

		const modalButton = document.createElement("button");
		modalButton.classList.add('button');
		modalButton.classList.add('button-primary');
		modalButton.innerHTML = 'Save';
		modalFooter.append(modalButton);


		modalContent.append(modalHeader);
		modalContent.append(modalBody);
		modalContent.append(modalFooter);

		modalDialog.append(modalContent);
		modal.append(modalDialog);

		document.body.append(modal);

		const backdrop = document.createElement("div");
		backdrop.classList.add('modal-backdrop');
		backdrop.classList.add('show');
		document.body.append(backdrop);

		document.body.classList.add('no-scroll');

		modalButton.addEventListener('click',function() {

			let modal = getParentByClassName(this,'modal');

			let inputs = modal.getElementsByClassName('modal-input');
			let newInputs = {};
			for (let input of inputs) {
				newInputs[input.id] = input.value;
			}


			// Update entry
			if (el.dataset.entry !== JSON.stringify(newInputs)) {
				el.dataset.entry = JSON.stringify(newInputs);

				let url = document.location.origin + '/wp-json/ttm-forms/v1/update/' + el.dataset.id;
				fetch(url, {
					cache: 'no-store',
					// credentials: credentials,
					method: 'POST',
					redirect: 'follow',
					headers: {
						'Accept': 'application/json',
						'Content-Type': 'application/json',
						'X-WP-Nonce': wpApiSettings.nonce
					},
					body: JSON.stringify({entry:newInputs})
				})
				.then(response => {
					if (!response.ok) {
						throw new Error(`HTTP error! Status: ${response.status}`);
					}
					return response.json();
				})
				.then(response => {
					let tr = getParentByTagName(el, 'tr');
					let tds = tr.querySelectorAll('td');

					tds.forEach(function(td){
						let colname = td.dataset.colname.toLowerCase();
						if (typeof response.entry[colname] !== 'undefined') {
							if(!td.classList.contains('column-primary')) {
								td.innerHTML = response.entry[colname];
							}
						}
						else if(colname !== 'date'){
							td.innerHTML = '';
						}
					})

				});
			}

			modal.remove();
			document.body.classList.remove('no-scroll');

			let bgs = document.getElementsByClassName("modal-backdrop")
			for (let bg of bgs) {
				bg.remove();
			}
		});
	}

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


		let parents = document.querySelectorAll( "[data-parent]" );
		let theParents = [];
		parents.forEach(function(parent){
			let theParent = parent.dataset[ 'parent' ];
			if( -1 === theParents.indexOf(theParent) ) {
				theParents.push( theParent );
			}
		})

		theParents.forEach(function(parent){
			let children = document.querySelectorAll( "[name='ttm_forms["+parent+"]']" );
			children.forEach(function(child){
				child.addEventListener('click',function(){

					let cs = document.querySelectorAll( "[data-parent='"+parent+"']" );
					cs.forEach(function(c){
						if( c.checked ) {
							c.click();
						}
					})

					let module = child.value;
					let el = document.getElementById( 'module-' + module );
					if( el ) {
						el.click();
					}
				});
			});
		})

		let entries = document.querySelectorAll( ".edit-row[data-entry]" );
		entries.forEach(function(entry){
			entry.addEventListener('click',function() {
				createModal(this)
			});
		});
	});
})()
