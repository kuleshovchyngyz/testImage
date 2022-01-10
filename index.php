<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TEST</title>
    <link href="style.css" rel="stylesheet" type="text/css">
    <script src="jquery-3.3.1.min.js"></script>

    <script type="text/javascript">
		
		var filesAll = [];
		var filesUploading = [];
		
		var isAdvancedUpload = function() {
			var div = document.createElement( 'div' );
			return ( ( 'draggable' in div ) || ( 'ondragstart' in div && 'ondrop' in div ) ) && 'FormData' in window && 'FileReader' in window;
		}();
			
		var minifyImg = function(dataUrl,newWidth,imageType="image/jpeg",resolve,imageArguments=1){
			var image, oldWidth, oldHeight, newHeight, canvas, ctx, newDataUrl;
			(new Promise(function(resolve){
			  image = new Image(); image.src = dataUrl;
              console.log('dataUrl:')
			  console.log(dataUrl);
			  //
			  setTimeout(() => {
				  resolve('Done : ');
			  }, 1000);
			  
			})).then((d)=>{
			  oldWidth = image.width; oldHeight = image.height;
			  //console.log([oldWidth,oldHeight]);
			  newHeight = Math.floor(oldHeight / oldWidth * newWidth);
			  //console.log(d+' '+newHeight);

			  canvas = document.createElement("canvas");
			  canvas.width = newWidth; canvas.height = newHeight;
			  //console.log(canvas);
			  ctx = canvas.getContext("2d");
			  ctx.drawImage(image, 0, 0, newWidth, newHeight);
			 // console.log(ctx);

			  newDataUrl = canvas.toDataURL(imageType, imageArguments);
			  resolve(newDataUrl);
                // console.log(newDataUrl);
            //
			});
		  };
		  
		function calcSizeKb(l, mb) {
			
			var s = (l * (3/4)) / 1024;
			if(mb) s = s / 1024;
			
			
			
			return s.toFixed(2);
			
		}
		
		
		function drawCircleProgress(circle) {

			var radius = circle.attr('r');
			//console.log(radius);
			var circumference = radius * 2 * Math.PI;

			circle.get(0).setAttribute('strokeDasharray', '${circumference} ${circumference}');
			circle.get(0).setAttribute('strokeDashoffset', '${circumference}');

		}		
		function setCircleProgress(circle, percent) {

			var radius = circle.attr('r');
			var circumference = radius * 2 * Math.PI;
			var offset = circumference - percent / 100 * circumference;
			circle.get(0).setAttribute('strokeDashoffset', offset);
		}
		
		function uploadResized(data, filename) {
            //var_dump(22);
			$('.img-preview[data-filename="'+filename+'"]').prepend('<div class="progress"><span></span></div>');
			//$('.img-preview[data-index='+index+']').prepend('<svg class="progress" width="120" height="120"><circle class="progress-ring" stroke="green" stroke-width="4" fill="transparent" r="50" cx="60" cy="60"/></svg>');
			
			//var $progress = $('.img-preview[data-index='+index+'] circle');
			var $progress = $('.img-preview[data-filename="'+filename+'"] span');
			//drawCircleProgress($progress);
            console.log(data);
		   $.ajax({
				url:'upload2.php',
				type:'post',
				contentType: 'application/octet-stream',
				processData: false,
				data: data,
				success:function(response){
					//console.log(response);
				},
				xhr: function(){
					var xhr = new XMLHttpRequest();

					xhr.upload.addEventListener('progress', function(e){
						if(e.lengthComputable){
							var uploadPercent = e.loaded / e.total;
							uploadPercent = (uploadPercent * 100);
							
							//setCircleProgress($progress, uploadPercent);
							
							
							$progress.text(uploadPercent.toFixed(1) + '%');
							$progress.width(uploadPercent.toFixed(1) + '%');

							
							if(uploadPercent == 100){
								$progress.text('Completed');
							}
							
						}
					}, false);

					return xhr;
				}
			});
			
		}
		
		async function resizeBeforeUpload(file, index) {
			
			//filesUploading.push(index);
          //  console.log('file:')
			// console.log(file);
			(new Promise(function(resolve){
				var reader = new FileReader();
				reader.onload = function(e) {
					
					//filesUploading.push(index);
					
					//resolve(e.target.result);
					resolve({file: file, filedata: e.target.result});
					
				}
				reader.readAsDataURL(file);
					
			  
			})).then((d)=>{
				
				let filename = d.file.name;
				var extn = filename.substring(filename.lastIndexOf('.') + 1).toLowerCase();
				var img_type = '';
				switch(extn) {
					case 'gif':
						img_type = 'image/gif';
					break;
					case 'png':
						img_type = 'image/png';
					break;	
					case 'jpg':
					case 'jpeg':		
					default:
						img_type = 'image/jpeg';
					break;
				}
				console.log('ext: '+extn);
				
				 minifyImg(d.filedata, 1600, img_type, (data)=> {
					console.log('resized: '+calcSizeKb(data.length)+' kb');
					
					summResizedSize(data.length);
					
					uploadResized(data, filename);
					
					
				});		

			});
		}
		
		var totalSize = 0;
		var totalResizedSize = 0;
		function summTotalSize(l) {
			totalSize += l;
			$('#total-size span').html(calcSizeKb(totalSize, true) + ' mb');
		}
		function summResizedSize(l) {
			totalResizedSize += l;
			
			$('#resized-size span').html(calcSizeKb(totalResizedSize, true) + ' mb');
			
		}
		
		function checkFile(name) {
			var extn = name.substring(name.lastIndexOf('.') + 1).toLowerCase();
			console.log('ext: '+extn);
			return (["gif","png","jpg","jpeg"].indexOf(extn) != -1);
		}
		
		
		function workFile(file, i) {
			  return new Promise((resolve/*, reject*/) => {

				var imgName = file.name;
				//console.log(imgName);
				
				if(!checkFile(imgName)) {
					//reject(i);
					resolve(i);
				} else {
					file.is_preview = 1;
					filesAll.push(file);
					let filename = file.name;
					var reader = new FileReader();
					reader.onload = function(e) {
						let img_data = e.target.result;
                        console.log(img_data.le)
                        console.log('original: '+calcSizeKb(img_data.length)+' kb');
						
						summTotalSize(img_data.length);
						
						var image_holder = $("#image-holder");
						var img_el = '<div class="img-preview" data-filename="'+filename+'"><div class="img-content" style="background-image:url('+img_data+');"></div></div>';
						image_holder.append(img_el);

					}
					reader.readAsDataURL(file);
					resolve(i);
				}
			  });
		}
		
		function startUploadFiles() {
			
			  let i;
			  let promises = [];
			  
			  if(!filesAll.length) return;
			  
			  for (i = 0; i < filesAll.length; i++) {
				resizeBeforeUpload(filesAll[i], i);
			  }
			  
		}
		
		function previewFiles(files) {
				filesAll = [];
				
			  let i;
			  let promises = [];
			  
			  for (i = 0; i < files.length; i++) {
				promises.push(workFile(files[i], i));
			  }
			  
			  Promise.all(promises)
				  .then((results) => {
					console.log("All done", results);					
					startUploadFiles();
				  })
				  .catch((e) => {
					console.log("All done with error");
					console.log(e);
					startUploadFiles();
			  });
		}
		
        $(document).ready(function(){
			
			if( isAdvancedUpload ) {
				$('#uploader').addClass('has-advanced-upload');
			}
			
			$('#uploader').on('drag dragstart dragend dragover dragenter dragleave drop', function(e){
				e.preventDefault();
				e.stopPropagation();
			});
			
			
			$('#uploader').on('dragover dragenter', function(e){
				$(this).addClass('is-dragover');
			});
			$('#uploader').on('dragleave dragend drop', function(e){
				$(this).removeClass('is-dragover');
			});
			
			$('#uploader').on('drop', function(e){
				if(e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files.length) {
					e.preventDefault();
					e.stopPropagation();
					previewFiles(e.originalEvent.dataTransfer.files);
				}
			});
			


			$("#file").on('change', function() {
			  previewFiles($(this)[0].files);
			});
            $("#but_upload").click(function(){

                var fd = new FormData();

				for(var i=0;i<$('#file')[0].files.length;i++){
					fd.append("file[]", $('#file')[0].files[i]);
				}

                $.ajax({
                    url:'upload.php',
                    type:'post',
                    data:fd,
                    contentType: false,
                    processData: false,
                    success:function(response){
                        if(response != 0){
                            $("#img").attr("src",response);
                            $('.preview img').show();
                        }else{
                           // alert('File not uploaded');
                        }
                    },
					xhr: function(){
						var xhr = new XMLHttpRequest();

						xhr.upload.addEventListener('progress', function(e){
							if(e.lengthComputable){
								var uploadPercent = e.loaded / e.total;
								uploadPercent = (uploadPercent * 100);

								$('#progress').text(uploadPercent + '%');
								$('#progress').width(uploadPercent + '%');

								if(uploadPercent == 100){
									$('#progress').text('Completed');
								}
							}
						}, false);

						return xhr;
					}
                });
            });

			
        });


    </script>

</head>
<body>

	<style>
		
#image-holder {display:flex;flex-wrap:wrap;}
#image-holder .img-preview {width:20%;padding:2px;border:1px solid #eee;position:relative;box-sizing:border-box;}
#image-holder .img-preview .img-content {position:relative;width:100%;height:0;padding-top:100%;background-position:center center;background-size:cover;background-repeat:no-repeat;}

#image-holder .img-preview .progress {width:100%;height:15px;background:red;display:block;position:absolute;z-index:1;bottom:0;left:0;}
#image-holder .img-preview .progress span {width:0%;height:15px;background:green;display:block;color:#fff;font-size:12px;}

/*

#image-holder .img-preview .progress {
	position:absolute;
	top:0;left:0;z-index:11;
}

#image-holder .img-preview .progress-ring {
  transition: 0.35s stroke-dashoffset;
  transform: rotate(-90deg);
  transform-origin: 50% 50%;
}
*/
				.box
				{
					font-size: 1.25rem; /* 20 */
					background-color: #c8dadf;
					position: relative;
					padding: 100px 20px;
				}
				.box.has-advanced-upload
				{
					outline: 2px dashed #92b0b3;
					outline-offset: -10px;

					-webkit-transition: outline-offset .15s ease-in-out, background-color .15s linear;
					transition: outline-offset .15s ease-in-out, background-color .15s linear;
				}
				.box.is-dragover
				{
					outline-offset: -20px;
					outline-color: #c8dadf;
					background-color: #fff;
				}
					.box__dragndrop,
					.box__icon
					{
						display: none;
					}
					.box.has-advanced-upload .box__dragndrop
					{
						display: inline;
					}
					.box.has-advanced-upload .box__icon
					{
						width: 100%;
						height: 80px;
						fill: #92b0b3;
						display: block;
						margin-bottom: 40px;
					}

					.box.is-uploading .box__input,
					.box.is-success .box__input,
					.box.is-error .box__input
					{
						visibility: hidden;
					}

					.box__uploading,
					.box__success,
					.box__error
					{
						display: none;
					}
					.box.is-uploading .box__uploading,
					.box.is-success .box__success,
					.box.is-error .box__error
					{
						display: block;
						position: absolute;
						top: 50%;
						right: 0;
						left: 0;

						-webkit-transform: translateY( -50% );
						transform: translateY( -50% );
					}
					.box__uploading
					{
						font-style: italic;
					}
					.box__success
					{
						-webkit-animation: appear-from-inside .25s ease-in-out;
						animation: appear-from-inside .25s ease-in-out;
					}
						@-webkit-keyframes appear-from-inside
						{
							from	{ -webkit-transform: translateY( -50% ) scale( 0 ); }
							75%		{ -webkit-transform: translateY( -50% ) scale( 1.1 ); }
							to		{ -webkit-transform: translateY( -50% ) scale( 1 ); }
						}
						@keyframes appear-from-inside
						{
							from	{ transform: translateY( -50% ) scale( 0 ); }
							75%		{ transform: translateY( -50% ) scale( 1.1 ); }
							to		{ transform: translateY( -50% ) scale( 1 ); }
						}

					.box__restart
					{
						font-weight: 700;
					}
					.box__restart:focus,
					.box__restart:hover
					{
						color: #39bfd3;
					}

					.js .box__file
					{
						width: 0.1px;
						height: 0.1px;
						opacity: 0;
						overflow: hidden;
						position: absolute;
						z-index: -1;
					}
					.js .box__file + label
					{
						max-width: 80%;
						text-overflow: ellipsis;
						white-space: nowrap;
						cursor: pointer;
						display: inline-block;
						overflow: hidden;
					}
					.js .box__file + label:hover strong,
					.box__file:focus + label strong,
					.box__file.has-focus + label strong
					{
						color: #39bfd3;
					}
					.js .box__file:focus + label,
					.js .box__file.has-focus + label
					{
						outline: 1px dotted #000;
						outline: -webkit-focus-ring-color auto 5px;
					}
						.js .box__file + label *
						{
							/* pointer-events: none; */ /* in case of FastClick lib use */
						}

					.no-js .box__file + label
					{
						display: none;
					}

					.no-js .box__button
					{
						display: block;
					}
					.box__button
					{
						font-weight: 700;
						color: #e5edf1;
						background-color: #39bfd3;
						display: block;
						padding: 8px 16px;
						margin: 40px auto 0;
					}
					.box__button:hover,
					.box__button:focus
					{
						background-color: #0f3c4b;
					}

	
	#progress-bar {width:100%;height:20px;background:red;display:none;}
	#progress {width:0%;height:20px;background:green;display:block;}
	</style>
    <div class="container">
		<div id="total-size">Общий размер: <span></span></div>
		<div id="resized-size">Оптимизированный размер: <span></span></div>
		
		<form id="uploader" class="box" method="post" action="" enctype="multipart/form-data">
            <div id='image-holder'>
                
            </div>
		  <div class="box__input">
			<input class="box__file" type="file" name="files[]" id="file" data-multiple-caption="{count} файлов выбрано" multiple />
			<label for="file"><strong>Выбрать фото</strong><span class="box__dragndrop"> или перетащить сюда</span>.</label>
			<button class="box__button" type="submit">Upload</button>
		  </div>
		  <div class="box__uploading">Загрузка…</div>
		  <div class="box__success">Готово!</div>
		  <div class="box__error">Ошибка! <span></span>.</div>
		  <div id="progress-bar"><div id="progress"></div></div>
		</form>
    </div>
</body>
</html>