var idd;
$(document).ready(function () {
  cart();
});

function viewproduct(id) {
  $.post(
    "/getproductdata",
    {
      id: id,
    },
    function (data, status) {
      let dataa = jQuery.parseJSON(data);
      idd = dataa[0]["id"];
      $("#product_name").text(dataa[0]["product_name"]);
      $("#product_price").text("$ " + dataa[0]["product_price"]);
      $("#product_desc").text(dataa[0]["product_desc"]);
      $(".product_img").attr("src", dataa[0]["product_img"]);
      $(".product_img").attr("href", dataa[0]["product_img"]);
      $("#star-rating").text(dataa[0]["rating"]);
    }
  );
}

function addtobag() {
  let quantity = $("#quickview-number").val();
  $.post(
    "/saveproductcart",
    {
      id: idd,
      quantity: quantity,
    },
    function (data, status) {
      console.log(data, status);
      if (status == "success") {
        $("#quickview-number").val("1");
        cart();
        swal("Product Added to Cart!", "", "success");
      }
    }
  );
}

function cart() {
  $.post("/getsessioncart", function (data, status) {
    let dataa = jQuery.parseJSON(data);
    $("#addcart").empty();
    var price = 0;
    if (Array.isArray(dataa) && dataa.length) {
      for (let i = 0; i < dataa.length; i++) {
        $.post(
          "/getproductdata",
          {
            id: dataa[i]["id"],
          },
          function (data, status) {
            data = jQuery.parseJSON(data);
            $("#totalprice").empty();
            $("#amount").empty();

            let d =
              "<div class='mb-4 d-flex'>" +
              "<a href='#' onclick='deleteproductfromcart(" +
              data[0]["id"] +
              ")' class='d-flex align-items-center mr-2 text-muted'><i class='fal fa-times'></i></a>" +
              "<div class='media w-100'>" +
              "<div class='w-60px mr-3'>" +
              "<img src='" +
              data[0]["product_img"] +
              "' alt='" +
              data[0]["product_img"] +
              "'>" +
              "</div>" +
              "<div class='media-body d-flex'>" +
              "<div class='cart-price pr-6'>" +
              "<p class='fs-14 font-weight-bold text-secondary mb-1'><span class='font-weight-500 fs-13 text-line-through text-body mr-1'>$39.00</span>$" +
              data[0]["product_price"] +
              "" +
              "</p>" +
              "<a href='' class='text-secondary'>" +  
              data[0]["product_name"] +
              "</a>" +
              "</div>" +
              "<div class='position-relative ml-auto'>" +
              "<div class='input-group'>" +
              "<a href='#' onclick='decrementcartproduct(" +
              data[0]["id"] +
              ")' class='down position-absolute pos-fixed-left-center pl-2'><i class='far fa-minus'></i></a>" +
              "<input type='hidden' name='id' class='number-cart w-90px px-6 text-center h-40px bg-input border-0' value='" +
              data[0]["id"] +
              "'>" +
              "<input type='number' disabled class='number-cart w-90px px-6 text-center h-40px bg-input border-0' value='" +
              dataa[i]["quantity"] +
              "'>" +
              "<a href='#'  onclick='incrementcartproduct(" +
              data[0]["id"] +
              ")'  class='up position-absolute pos-fixed-right-center pr-2'><i class='far fa-plus'></i>" +
              "</a>" +
              "</div>" +
              "</div>" +
              "</div>" +
              "</div>" +
              "</div>";
            price =
              price +
              parseInt(data[0]["product_price"]) *
                parseInt(dataa[i]["quantity"]);
            $("#addcart").append(d);
            $("#totalprice").append("$ " + price);
            $("#amount").append("$ " + price);

          }
        );
      }
      $("#totalcart").text(dataa.length);
    } else {
      $("#totalprice").empty();
      $("#amount").empty();

      let d =
        "<div class='mb-4 d-flex'>" + 
        "<a href='' class='d-flex align-items-center mr-2 text-muted'><i class='fal fa-times'></i></a>" +
        "<div class='media w-100'>" +
        "<div class='w-60px mr-3'>" +
        "<img src='' alt=''>" +
        "</div>" +
        "<div class='media-body d-flex'>" +
        "<div class='cart-price pr-6'>" +
        "<p class='fs-14 font-weight-bold text-secondary mb-1'><span class='font-weight-500 fs-13 text-line-through text-body mr-1'></span>$0" +
        "</p>" +
        "<a href='' class='text-secondary'>Please Add Product To Cart</a>" +
        "</div>" +
        "<div class='position-relative ml-auto'>" +
        "<div class='input-group'>" +
        "<a href='#' onclick='decrementcartproduct(" +
        data[0]["id"] +
        ")'  class='down position-absolute pos-fixed-left-center pl-2'><i class='far fa-minus'></i></a>" +
        "<input type='hidden' name='id' class='number-cart w-90px px-6 text-center h-40px bg-input border-0' value='0'>" +
        "<input type='number' class='number-cart w-90px px-6 text-center h-40px bg-input border-0' value='0'>" +
        "<a href='#'onclick='incrementcartproduct(" +
        data[0]["id"] +
        ")'  class='up position-absolute pos-fixed-right-center pr-2'><i class='far fa-plus'></i>" +
        "</a>" +
        "</div>" +
        "</div>" +
        "</div>" +
        "</div>" +
        "</div>";
      price = 0;
      $("#addcart").append(d);
      $("#totalcart").text("0");
      $("#totalprice").append("$ " + price);
      $("#amount").append("$ 0.00 ");

    }
  });
}

function deleteproductfromcart(id) {
  $.post(
    "/deleteproductfromcart",
    {
      id: id,
    },
    function (data, status) {
      console.log(data, status);
      cart();
    }
  );
}

function decrementcartproduct(id) {
  $.post(
    "/decrementproductcart",
    {
      id: id,
    },
    function (data, status) {
      console.log("decrementcart product");
      console.log(data, status);
      if (status) {
        cart();
      }
    }
  );
}

function incrementcartproduct(id) {
  $.post(
    "/incrementproductcart",
    {
      id: id,
    },
    function (data, status) {
      console.log("incrementcart product");
      console.log(data, status);
      if (status) {
        cart();
      }
    }
  );
}
