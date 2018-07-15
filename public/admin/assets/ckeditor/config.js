/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */
 
CKEDITOR.config.filebrowserImageUploadUrl = "/upload?command=QuickUpload&type=Images";
CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For complete reference see:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config
	config.toolbarGroups = [
		{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'NumberedList', 'BulletedList', '-', 'Outdent', '-', 'JustifyCenter' ] },
		// { name: 'editing',  groups: [ 'find', 'selection', 'spellchecker' ] },
		{ name: 'links',       groups: [ 'links', 'Unlink' ] },
		{ name: 'insert' },
		{ name: 'document',    groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'format', groups: ['RemoveFormat'] },
		{ name: 'forms' },
		{ name: 'tools' },
		{ name: 'others' },
		'/',
		{ name: 'styles' },
		{ name: 'colors' },
	];

	// Remove some buttons provided by the standard plugins, which are
	// not needed in the Standard(s) toolbar.
	config.removeButtons = 'Underline,Subscript,Superscript';

	// Set the most common block elements.
	config.format_tags = 'p;h1;h2;h3;pre';

	// Simplify the dialog windows.
	// config.removeDialogTabs = 'image:advanced;link:advanced';
	config.removeButtons = 'Underline,Subscript,Superscript,Cut,Copy,Paste,PasteText,PasteFromWord,Scayt,Anchor,HorizontalRule,SpecialChar,Strike,Blockquote,Styles,About,Format,Source';
};
//setting default width as 100% for table
CKEDITOR.on('dialogDefinition', function( ev ) {
	
	  var diagName = ev.data.name;
	  var diagDefn = ev.data.definition;

	  if(diagName === 'table') { //if dialog name equal to table
	    var infoTab = diagDefn.getContents('info');
	    
	    var width = infoTab.get('txtWidth');
	    width['default'] = "100%";  
	  }
});