 jQuery(function ($) {

    var price = 0;
    var hostname = window.location.origin

   $(".cart").change(function() {
      var fmdata = $(".cart").serialize();
      var product = $("#product").val();

      var url = 'https://manage.bilahpro.com/woohq?' + fmdata ;
      $.post( url, function( response) {
          console.log(response);
          var data = response.data;
          var price = Number(data.price).toFixed(2);
           $('#product_total_price .price').html( 'RM'+ price);
           $("#total_price").val(price);
            
          if(product == 'namecard') {
            var preview = hostname + "/wp-content/plugins/woohq/assets/images/" + data.preview;
            $('#paparan').attr("src", preview);
          }

      },'json');
      
      var harga = $("#total_price").val();
      var product_total = parseFloat(harga);
      $('#product_total_price .price').html( 'RM' + product_total.toFixed(2));
      
   });

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

        //console.log(previous, this.value);
        previous = this.value;
    });
});