
$('#epicSwitch').click(function(){
  if ($('#epicSwitch input[type="checkbox"]').prop('checked')) {
    $('#epiceditor, .epicControl').fadeIn();
    $('#text').fadeOut();
  } else {
    $('#epiceditor, .epicControl').fadeOut();
    $('#text').fadeIn();
  }
});

$('#epicPreviewSwitch').click(function(){
  if ($('#epicPreviewSwitch input[type="checkbox"]').prop('checked')) {
    editor.preview();
  } else {
    editor.edit();
  }
});

$('#epicFullscreenSwitch').click(function(){
  if (editor.is('fullscreen')) {
    $('#epicFullscreenSwitch input[type="checkbox"]').attr('checked', false);
    return;
  }
  editor.enterFullscreen();
});

var opts = {
  container: 'epiceditor',
  textarea: 'text',
// disable until there is a file manager
  clientSideStorage: false,
//  localStorageName: 'epiceditor',
  useNativeFullscreen: true,
  basePath: PubDirUrl+'/javascripts/vendor/epiceditor',
  theme: {
    base: '/themes/base/epiceditor.css',
    preview: '/themes/preview/preview-dark.css',
    editor: '/themes/editor/epic-dark.css'
  },
  button: {
    preview: true,
    fullscreen: true,
    bar: 'auto',
  },
  focusOnLoad: false,
  shortcut: {
    modifier: 18,
    fullscreen: 70,
    preview: 80
  },
  string: {
    togglePreview: 'Toggle Preview Mode',
    toggleEdit: 'Toggle Edit Mode',
    toggleFullscreen: 'Enter Fullscreen'
  },
  autogrow: {
    minHeight: 100,
    maxHeight: 400,
  },
}

var editor = new EpicEditor(opts).load(function(){
  $('#text').fadeOut();
//  this.rename('epiceditor', PageName);
//  files = this.getFiles();
//  for (var f in files) {
//    nameSpan = '<span class="name">'+f+'</span>';
//    d = new Date(files[f].modified);
//    timeSpan = '<span class="time">'+moment(d).calendar()+'</span>';
//    aItem = '<a href="#" class="list-group-item">'+nameSpan+' '+timeSpan+'</a>';
//    $('#localFileList').prepend(aItem);
//  }
});

