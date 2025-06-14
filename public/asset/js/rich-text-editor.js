tinymce.init({
    selector: "#tinymce",
    plugins: "image link lists media",
    toolbar:
        "undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | align lineheight | tinycomments | checklist numlist bullist indent outdent | emoticons charmap | removeformat",
    menubar: true,
    paste_data_images: true,
    statusbar: true,
/* enable title field in the Image dialog*/
image_title: true,
/* enable automatic uploads of images represented by blob or data URIs*/
automatic_uploads: true,
/*
  URL of our upload handler (for more details check: https://www.tiny.cloud/docs/configure/file-image-upload/#images_upload_url)
  images_upload_url: 'postAcceptor.php',
  here we add custom filepicker only to Image dialog
*/
file_picker_types: 'image',
/* and here's our custom image picker*/
file_picker_callback: (cb, value, meta) => {
  const input = document.createElement('input');
  input.setAttribute('type', 'file');
  input.setAttribute('accept', 'image/*');

  input.addEventListener('change', (e) => {
    const file = e.target.files[0];

    const reader = new FileReader();
    reader.addEventListener('load', () => {
      /*
        Note: Now we need to register the blob in TinyMCEs image blob
        registry. In the next release this part hopefully won't be
        necessary, as we are looking to handle it internally.
      */
      const id = 'blobid' + (new Date()).getTime();
      const blobCache =  tinymce.activeEditor.editorUpload.blobCache;
      const base64 = reader.result.split(',')[1];
      const blobInfo = blobCache.create(id, file, base64);
      blobCache.add(blobInfo);

      /* call the callback and populate the Title field with the file name */
      cb(blobInfo.blobUri(), { title: file.name });
    });
    reader.readAsDataURL(file);
  });

  input.click();
},
content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }',
    ai_request: (request, respondWith) =>
        respondWith.string(() =>
            Promise.reject("See docs to implement AI Assistant")
        ),
});
