 jQuery(function ($) {

    var price = 0;
    var hostname = window.location.origin

    var url = window.location.href;
    if (url.includes("/product/")  ) {
      calcPrice();

     $(".cart").change(function() {
        calcPrice();
     });

   }

    var previous;

    $(".unit").on('focus', function () {
        previous = this.value;
    });


    $(".unit").change(function() {
      var width, height; 
       if(previous == 'mm' && this.value == 'cm'){
            width = $(".width").val() / 10;
            height= $(".height").val() / 10;
            $(".width").val(width.toFixed(1));
            $(".height").val(height.toFixed(1));
       }

       if(previous == 'mm' && this.value == 'in'){
            width = $(".width").val() / 25.4;
            height= $(".height").val() / 25.4;
            $(".width").val(width.toFixed(2));
            $(".height").val(height.toFixed(2));
       }

       if(previous == 'cm' && this.value == 'mm'){
            width = $(".width").val() * 10;
            height= $(".height").val() * 10;
            $(".width").val(width.toFixed(0));
            $(".height").val(height.toFixed(0));
       }

       if(previous == 'cm' && this.value == 'in'){
            width = $(".width").val() / 2.54;
            height= $(".height").val() / 2.54;
            $(".width").val(width.toFixed(2));
            $(".height").val(height.toFixed(2));
       }

       if(previous == 'in' && this.value == 'mm'){
            width = $(".width").val() * 25.4;
            height= $(".height").val() * 25.4;
            $(".width").val(width.toFixed(0));
            $(".height").val(height.toFixed(0));
       }

       if(previous == 'in' && this.value == 'cm'){
            width = $(".width").val() * 2.54;
            height= $(".height").val() * 2.54;
            $(".width").val(width.toFixed(2));
            $(".height").val(height.toFixed(2));
       }

       if(previous == 'in' && this.value == 'ft'){
            width = $(".width").val() / 12;
            height= $(".height").val() / 12;
            $(".width").val(width.toFixed(2));
            $(".height").val(height.toFixed(2));
       }

       if(previous == 'ft' && this.value == 'in'){
            width = $(".width").val() * 12;
            height= $(".height").val() * 12;
            $(".width").val(width.toFixed(2));
            $(".height").val(height.toFixed(2));
       }

        //console.log(previous, this.value);
        previous = this.value;
    });

  function calcPrice(){
    var price = 0;
    var hostname = window.location.origin;
    var fmdata = $(".cart").serialize();
    var product = $("#product").val();
    var pid = document.querySelector('.status-publish').getAttribute('id').replace("product-", "");
    var url = hostname + '/wp-json/woohq/getprice?pid=' + pid + '&' + fmdata ;

    $.post( url, function( response) {
        console.log(response);

        var data = response.data;
        var post = data.post;

        if(post.wcpa_field_key_checker) {
          var price = Number(data.price).toFixed(2);

           $('.price').html( 'RM'+ price);
           $("#total_price").val(price);
            
          if(product == 'namecard') {
            $('#ref').val(data.ref);
            var preview = hostname + "/wp-content/plugins/woohq/assets/images/" + data.preview;
            $('#paparan').attr("src", preview);

            var pricelist = data.pricelist;
            $('.price_table').html( pricelist );
          }

          if(product == 'sticker-sekolah') {
            var preview = data.preview;
            $('#ref').val(data.ref);
            $('#paparan').attr("src", preview);
          }

          if(product == 'sticker-sekolah-pdf') {
            var preview = hostname + "/wp-content/plugins/woohq/web/viewer.html?file=" + data.preview;
            $('#ref').val(data.ref);
            $('#gpreview').attr("src", preview);
          }

          if(product == 'bunting') {
            var dt = new Date(); 

            var preview = hostname + "/wp-content/plugins/woohq/web/viewer.html?file=" + data.preview;
            var display = hostname + "/wp-content/plugins/woohq/assets/images/" + data.display + '?dt=' + dt.getTime();
            $('#ref').val(data.ref);
            $('#gpreview').attr("src", preview);
            $('.wp-post-image').removeAttr('data-srcset');​ // Remove the src
            $('.wp-post-image').removeAttr('srcset');​ // Remove the src
            $('.wp-post-image').attr("src", display);

            var list_machine = data.list_machine;
            var list_material = data.list_material;
            var list_hanger = data.list_hanger;
            var list_stand = data.list_stand;

            var posto = data.post;
            buildList('material', list_material, posto.material);
            buildList('machine', list_machine, posto.machine);
            buildList('hanger', list_hanger, posto.hanger);
            buildList('stand', list_stand, posto.stand);

            function buildList(target, dat, posto) {
                var $el = $('.'+ target);
                $el.empty(); // remove old options
                $.each(dat, function(key, value) {
                  $el.append($("<option></option>")
                     .attr("value", key).text(value));
                });

                $el.val(posto);
            }

          }


          if(product == 'sticker-sekolah-test') {
            var preview = data.preview;
            $('#ref').val(data.ref);
            previewSticker(preview);
            //$('.pdfemb-viewer').attr("src", preview);
          }

          if(product == 'sticker-kelas') {
            var preview = hostname + "/wp-content/plugins/woohq/web/viewer.html?file=" + data.preview;
            $('#ref').val(data.ref);
            $('#jumlah').val(data.jumlah);
            $('#total_sticker').val(data.total_sticker);
            $('#per_name').val(data.per_name);
            $('#students').val(data.max);
            $('#gpreview').attr("src", preview);
          }
        
          var harga = $("#total_price").val();
          var product_total = parseFloat(harga);
          $('.price').html( 'RM' + product_total.toFixed(2));


        } else {
          //do nothing
        }


        

    },'json');
    
    

  }

  var pdfjsLib = window['pdfjs-dist/build/pdf'];
  pdfjsLib.GlobalWorkerOptions.workerSrc = '/wp-content/plugins/woohq/includes/pdf.worker.js';

  function previewSticker(url){
    var loadingTask = pdfjsLib.getDocument(url);
    loadingTask.promise.then(function(pdf) {

      var pageNumber = 1;
      pdf.getPage(pageNumber).then(function(page) {
        
        var scale = 1;
        var viewport = page.getViewport({scale: scale});

        // Prepare canvas using PDF page dimensions
        var canvas = document.getElementById('gpreview');
        var context = canvas.getContext('2d');
        canvas.height = viewport.height;
        canvas.width = viewport.width;

        var renderContext = {
          canvasContext: context,
          viewport: viewport
        };
        var renderTask = page.render(renderContext);
        renderTask.promise.then(function () {
        });
      });
    });
  }

});

