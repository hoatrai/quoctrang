jQuery(document).ready(function ($) {
    // Fetch table list
// Function to get query parameters from the URL
    function getParameterByName(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }
    $.ajax({
        url: ajaxurl,
        method: "POST",
        dataType: "json",
        data: { action: "get_jqgrid_tables" },
        success: function (response) {
            if (!response.success || !response.tables) {
                console.error("Error: No table list received");
                return;
            }

            var $tableSelector = $("#tableSelector");
            $tableSelector.empty(); // Clear existing options

            // Populate dropdown
            $.each(response.tables, function (index, table) {
                $tableSelector.append($("<option>", {
                    value: table,
                    text: table
                }));

            });

            // Load first table by default
           /* if (response.tables.length > 0) {
                loadGrid(response.tables[0]);
            }*/
            // Get table name from URL
            var tableFromURL = getParameterByName("page");
            console.log(tableFromURL);

            // Select table from URL if available, otherwise load first table
            var selectedTable = tableFromURL && response.tables.includes(tableFromURL) ? tableFromURL : response.tables[0];

            $tableSelector.val(selectedTable);
            loadGrid(selectedTable); // Load the selected table

            // Change event for table selection
            $tableSelector.on("change", function () {
                loadGrid($(this).val());
            });
        },
        error: function () {
            console.error("Failed to fetch table list");
        }
    });

    // Function to load jqGrid
    function loadGrid(selectedTable) {
        $.ajax({
            url: ajaxurl,
            method: "POST",
            dataType: "json",
            data: { action: "get_jqgrid_colmodel", table: selectedTable },
            success: function (response) {
                if (!response.success || !response.colModel) {
                    console.error("Error: No colModel data received");
                    return;
                }

                var colModel = response.colModel;




                $grid = $("#jqGrid");
                $("#jqGrid").jqGrid("GridUnload"); // Unload previous grid
                $("#jqGrid").jqGrid({
                    url: ajaxurl,
                    datatype: "json",
                    mtype: "POST",
                    postData: {
                        action: "load_jqgrid_data",
                        table: selectedTable
                    },
                    colModel: colModel,
                    jsonReader: {
                        root: "rows",
                        repeatitems: false,
                        id: "id"
                    },
                    viewrecords: true,
                    sortorder: "desc",
                    sortable: true,
                    rowNum: 20,
                    autowidth: true,
                    loadOnce: false,
                    multiselect: false,
                    forceFit: false,  // Allow full width of columns
                    height: "100%",
                    pager: "#jqGridPager",
                    loadError: function (xhr, status, error) {
                        console.log("Error:", status, error);
                        console.log("Response Text:", xhr.responseText);
                    },
                    loadComplete: function () {

                        $(".ui-search-toolbar select").select2({
                            width: 'resolve',
                            placeholder: "Chọn giá trị",
                            allowClear: true,
                        }).next('.select2-container').find('.select2-selection--single').css({
                            'height': '31px',
                            'line-height': '31px'
                        });
                        console.log("Data loaded successfully.");
                        $("option[value=100000000]").text('All');
                        $("tr.jqgrow:odd").css({ "backgroundColor": "#f0f2f6" });
                        $(".ui-jqgrid .ui-jqgrid-htable th").css({
                            "border": "1px solid #cfd8e3",
                            "background": "linear-gradient(to bottom, #f0f2f6, #d9e0ea)",
                            "box-shadow": "0 3px 5px rgba(0, 0, 0, 0.2)",
                            "text-shadow": "1px 1px 0px #fff",
                            "font-weight": "bold",
                            "padding": "3px"
                        });
                        $(".ui-jqgrid-sortable").css({ "font-weight": "bold", "font-size": "14px" });
                        $("input[type=checkbox], input[type=radio]").css({ "visibility": "inherit", "display": "inline" });
                        $(".ui-jqgrid-htable").css({ "height": "50px" });
                        $(".ui-jqgrid tr.jqgrow td").css({ "height": "40px", "border": "1px solid #FFF", "font-size": "13px" });
                        $(".ui-jqgrid .ui-jqgrid-hdiv").css({ "border-radius": "6px 6px 0 0" });
                    }
                });
                // tao thanh cuon ngang khi cot qua dai
                // Resize grid when window resizes
               /* $(window).on("resize", function () {
                    $("#jqGrid").setGridWidth($(window).width() - 30);
                }).trigger("resize");*/

                $("#createNewBtn").click(function () {
                    $("#jqGrid").jqGrid("editGridRow", "new", {
                        // Add options
                        url: ajaxurl,
                        mtype: "POST",
                        editData: { action: "add_jqgrid_data", table: selectedTable},
                        closeAfterAdd: true,
                        reloadAfterSubmit: true,
                        onClose: function () {
                            $(".select2-dropdown").hide();
                            return true;
                        },
                        width:1000,
                        beforeInitData: function () {
                            selRowId = $grid.jqGrid('getGridParam', 'selrow');
                        },
                        recreateForm: true,
                        beforeSubmit: function (postdata, formid) {
                            // Remove commas before saving
                            postdata.giaban = postdata.giaban.replace(/,/g, ''); // Remove commas
                            postdata.giathue = postdata.giathue.replace(/,/g, ''); // Remove commas
                            postdata.giachot = postdata.giachot.replace(/,/g, ''); // Remove commas
                            return [true, '', postdata];
                        },
                        beforeShowForm: function ($form) {

                            $("#sData").click(function() {
                                var formValid = true;  // Flag to check if form is valid
                                var firstInvalidField = null;  // To store the first invalid field

                                // Loop through all input fields and select2 fields to check for emptiness or invalidity
                                $("form input, form select").each(function() {
                                    // Check if the field is empty
                                    if ($(this).val() === "" || $(this).hasClass('invalid')) {
                                        if (!firstInvalidField) {
                                            firstInvalidField = $(this);  // Store the first invalid field
                                        }
                                        formValid = false;  // Set the form as invalid
                                    }

                                    // For select2 fields, check if the select2 dropdown has an empty value
                                    if ($(this).is("select") && $(this).hasClass("select2-hidden-accessible") && $(this).val() === null) {
                                        if (!firstInvalidField) {
                                            firstInvalidField = $(this);  // Store the first invalid select2 field
                                        }
                                        formValid = false;  // Set the form as invalid
                                    }
                                });

                                // If form is invalid, scroll to the first invalid field
                                if (!formValid && firstInvalidField) {
                                    // Check if the invalid field is a select2 field and scroll to its container
                                    if (firstInvalidField.hasClass("select2-hidden-accessible")) {
                                        firstInvalidField = firstInvalidField.siblings(".select2-container");  // Get the select2 container
                                    }

                                    // Scroll to the field
                                    $('html, body').animate({
                                        scrollTop: firstInvalidField.offset().top - 500  // Scroll to the field
                                    }, 500); // 500 ms duration for scrolling
                                }

                                return formValid;  // Return the validation result (true if valid, false otherwise)
                            });

                            // Select the input field for the specific column (replace 'column_name' with your column identifier)




                            // fix template table
                            //=========================
                            var $table = $form.find("table");

                            // Apply table styles
                            $table.css({
                                "width": "100%",
                                "border-collapse": "collapse",
                                "display": "flex",
                                "flex-wrap": "wrap",
                                "gap": "34px",
                                "overflow": "hidden", // Ensures no extra width
                                'padding': '0px 0px 0px 30px',
                            });

                            // Ensure form rows align properly
                            $table.find("tbody").css({
                                "display": "flex",
                                "flex-wrap": "wrap",
                                "gap": "34px",
                                "width": "100%",
                                "overflow": "hidden"
                            });


                            // Style form-row properly
                            $(".form-row").css({
                                "display": "flex",
                                "justify-content": "space-between",
                                "width": "100%",
                                "gap": "15px",
                                "overflow": "hidden"
                            });


                            // Style each field container to not be too wide
                            $table.find("tr").css({
                                "display": "flex",
                                "flex-direction": "column",
                                "flex": "1",
                                "position":"relative",
                                "margin":"20px 0 20px 0px",
                                "max-width": "100%" // Ensures no overflow
                            });


                            // Get all table rows (tr) and exclude the first 2 rows from the grouping
                            var $rows = $table.find("tr"); // Select all table rows

// Remove all existing tbody elements (reset structure)
                            $table.find("tbody").remove();

// Re-create the first 2 rows without any grouping
                            var $newTbody = $("<tbody></tbody>").css({"width":"100%","height":"30px"}); // Make sure the first tbody has full width
                            $table.append($newTbody); // Append the new tbody to the table

// Add the first two rows directly to the new tbody
                            $newTbody.append($rows.slice(0, 2)); // Add first two rows as they are

// Create a new tbody for grouping after the first two rows
                            var $groupedTbody = $("<tbody></tbody>");
                            $table.append($groupedTbody); // Append the new tbody for grouped rows

// Now, group every 3 rows into the $groupedTbody (after first two)
                            $rows.slice(2).each(function (index) {
                                // Group every 3rd row starting from the 3rd one
                                if (index % 4 === 0 && index !== 0) {
                                    $groupedTbody = $("<tbody></tbody>"); // Create a new tbody for every group
                                    $table.append($groupedTbody); // Append a new tbody for each group of 3
                                }

                                // Append the current row to the grouped tbody
                                $groupedTbody.append($(this));
                            });

// Reinitialize Select2 on the select elements after DOM manipulation


//==========================



                            //select2
                            $form.find("select").each(function () {
                                $(this).select2({
                                    width: '100%',
                                    placeholder: 'Chọn thông tin',
                                    allowClear: false,
                                    minimumResultsForSearch: 0 // Enable search functionality
                                })
                            });

                            // Apply Select2 styling
                            applySelect2Styles();


                           /* var $thoigianthue = $('input[name="thoigianthue"]');
                            if ($thoigianthue.length > 0) {
                                $thoigianthue.datepicker({
                                    dateFormat: 'yy-mm-dd',
                                    changeMonth: true,
                                    changeYear: true
                                });
                            };*/
                            var $hethanthue = $('input[name="hethanthue"]');
                            if ($hethanthue.length > 0) {
                                $hethanthue.datepicker({
                                    dateFormat: 'yy-mm-dd',
                                    changeMonth: true,
                                    changeYear: true
                                });
                            }




                            $form.addClass("custom-jqgrid-form");
                            // Modify the form header style
                            $form.parent().find('.ui-jqdialog-titlebar').css({
                                'background': '#FFA500', // Orange background
                                'color': 'white',
                                'font-size': '16px',
                                'font-weight': 'bold',
                                'padding': '10px',
                                'border-radius': '8px 8px 0 0'
                            });

                            // Style input fields
                            $form.find('input, select, textarea').css({
                                'width': '100%',
                                'padding': '8px',
                                'border': '1px solid #ccc',
                                'border-radius': '5px',
                                'font-size': '13px',
                            });

                            // Style the form buttons
                            $form.parent().find('.ui-jqdialog-buttonset button').css({
                                'background': '#4CAF50',
                                'color': 'white',
                                'font-size': '14px',
                                'padding': '8px 12px',
                                'border-radius': '5px',
                                'border': 'none',
                                'cursor': 'pointer',
                                'transition': '0.3s'
                            }).hover(
                                function () {
                                    $(this).css('background', '#388E3C');
                                },
                                function () {
                                    $(this).css('background', '#4CAF50');
                                }
                            );

                            // Change title text
                            $(".ui-jqdialog-title").html('<img src="https://s.w.org/images/core/emoji/15.0.3/svg/2795.svg" style="width: 18px; height: 18px; filter: invert(40%) sepia(90%) saturate(3000%) hue-rotate(90deg);"> Thêm Mới');

                            // Apply modern styling to the header
                            $(".ui-jqdialog-titlebar").css({
                                "color": "#ffa500",          // White text
                                "font-size": "18px",
                                "font-weight": "600",
                                "padding": "5px",
                                "border-radius": "2px 2px 0 0",
                                "border-bottom": "3px solid #4caf50" // Darker green border bottom
                            });

                            // Style the close button for better UX
                            $(".ui-jqdialog-titlebar-close").css({
                                "background": "transparent",
                                "border": "none",
                                "cursor": "pointer",
                                "padding": "4px",
                                "transition": "0.3s"
                            });

                            // Hover effect for close button
                            $(".ui-jqdialog-titlebar-close span").css({
                                "color": "#fff",
                                "font-size": "16px"
                            });

                            $(".ui-jqdialog-titlebar-close").hover(
                                function () { $(this).css("opacity", "0.7"); },
                                function () { $(this).css("opacity", "1"); }
                            );


                            var dlgDiv = $("#editmod" + $grid[0].id);
                            var parentDiv = dlgDiv.parent();
                            var dlgWidth = dlgDiv.width();
                            var parentWidth = parentDiv.width();

                            // Đặt modal lên sát mép trên màn hình
                            dlgDiv.css({
                                "top": "-70px",  // Đẩy modal lên sát viền trên màn hình
                                "left": Math.round((parentWidth - dlgWidth) / 2) + "px",
                               /* "z-index": "9999", // Luôn nằm trên các phần tử khác
                                "position": "fixed" // Giữ nguyên vị trí khi cuộn trang*/
                            });



                            // Fix the main dialog to avoid horizontal scrolling
                            var $dialog = $form.closest(".ui-jqdialog");
                            $dialog.css({
                                "border-radius": "10px",
                                "background": "#fff",
                                "box-shadow": "0px 4px 10px rgba(0,0,0,0.2)",
                                //"max-width": "800px",
                                "width": "70%",  // Prevents form from being too wide
                                "overflow": "hidden", // Prevents horizontal scrolling,
                                "padding" :"0px",
                            });

                            // Style labels
                            $form.find("td.CaptionTD").css({
                                "font-weight": "bold",
                                "color": "#333",
                                //"width": "100%",
                                "margin-bottom": "5px",
                                "text-align": "left",
                                "position":"absolute",
                                "top": "-10px",
                                "z-index": "1",
                            "left": "8px",
                            "height": "20px",
                               "background" : "linear-gradient(to bottom, transparent 50%, rgb(255, 255, 255) 50%, rgb(255, 255, 255) 51%, transparent 65%)",

                            });

                            // Style input fields
                            $form.find("td.DataTD input, td.DataTD select").css({
                                "width": "100%",
                                "padding": "8px",
                                "border": "1px solid #ccc",
                                "border-radius": "5px",
                                "font-size": "14px",
                                "box-sizing": "border-box",
                                "height": "40px",
                                "transition": "all 0.3s ease-in-out" // Hiệu ứng mượt khi thay đổi trạng thái
                            });

                            // Hover effect
                            $form.find("td.DataTD input, td.DataTD select").hover(
                                function () {
                                    $(this).css("border-color", "#007bff"); // Khi hover, viền đổi màu xanh
                                },
                                function () {
                                    $(this).css("border-color", "#ccc"); // Khi không hover, trở lại màu cũ
                                }
                            );

                            // Focus effect
                            $form.find("td.DataTD input, td.DataTD select").on("focus", function () {
                                $(this).css({
                                    "border-color": "#007bff",
                                    "box-shadow": "0 0 5px rgba(0, 123, 255, 0.5)"
                                });
                            }).on("blur", function () {
                                $(this).css({
                                    "border-color": "#ccc",
                                    "box-shadow": "none"
                                });
                            });

                            // Fix button alignment
                            $(".EditButton").css({
                                "display": "flex",
                                "justify-content": "center",
                                "gap": "15px",
                                "margin-top": "20px"
                            });

                            // Style Submit button
                            $("#sData").html('<span style="color: #fff;">✔ Tiếp Tục</span>').css({
                                "background": "#4CAF50",
                                "border-radius": "5px",
                                "padding": "10px 20px",
                                "font-weight": "bold",
                                "box-shadow": "0 2px 4px rgba(0,0,0,0.2)",
                                "border": "none"
                            });

                            // Style Cancel button
                            $("#cData").html('<span style="color: #fff;">✖ Hủy</span>').css({
                                "background": "#d9534f",
                                "border-radius": "5px",
                                "padding": "10px 20px",
                                "font-weight": "bold",
                                "box-shadow": "0 2px 4px rgba(0,0,0,0.2)",
                                "border": "none"
                            });



                            // Hide form error message
                            $("#FormError").hide();
                            $('[value="_empty"]').hide();

                            // handle ajax province, district, wards
                            $('#province').on('change', function() {
                                $('#district').prop('disabled', true);  // Disable the district dropdown
                                $('#district').empty().append('<option value="">Tải thông tin</option>');
                                var province_id = $(this).val(); // Get selected province ID
                                if (province_id) {
                                    // If a province is selected, make an AJAX request
                                    $.ajax({
                                        url: ajaxurl, // WordPress AJAX URL
                                        method: 'POST',
                                        data: {
                                            action: 'load_districts', // Custom action to load districts
                                            province_id: province_id
                                        },
                                        success: function(response) {
                                           // console.log(response); // Check the response in the browser console to see if it has the data
                                            $('#district').prop('disabled', false);  // Enable the district dropdown
                                            // Empty the district dropdown first
                                            $('#district').empty().append('<option value="">Chọn thông tin</option>');
                                            var districts = response.data.data || []; // Get actual districts array

                                            if (Array.isArray(districts) && districts.length > 0) {
                                                $('#district').empty().append('<option value="">Chọn thông tin</option>');

                                                $.each(districts, function(index, district) {
                                                    $('#district').append('<option value="' + district.id + '">' + district.name + '</option>');
                                                });

                                                $('#district').trigger('change'); // Refresh Select2
                                            } else {
                                                console.warn("No districts found:", response);
                                                $('#district').empty().append('<option value="">No districts available</option>');
                                                $('#district').trigger('change');
                                            }
                                        }
                                    });
                                } else {
                                    // Clear the district select dropdown if no province is selected
                                    $('#district').empty().append('<option value="">Chọn thông tin</option>');
                                }
                            });

                            $('#district').on('change', function() {
                                $('#wards').prop('disabled', true);  // Disable the district dropdown
                                $('#wards').empty().append('<option value="">Tải thông tin</option>');
                                var district_id = $(this).val(); // Get selected district ID
                                if (district_id) {
                                    // If a district is selected, make an AJAX request
                                    $.ajax({
                                        url: ajaxurl, // WordPress AJAX URL
                                        method: 'POST',
                                        data: {
                                            action: 'load_wards', // Custom action to load districts
                                            district_id: district_id
                                        },
                                        success: function(response) {
                                            // console.log(response); // Check the response in the browser console to see if it has the data
                                            $('#wards').prop('disabled', false);  // Enable the district dropdown
                                            // Empty the district dropdown first
                                            $('#wards').empty().append('<option value="">Chọn thông tin</option>');
                                            var wards = response.data.data || []; // Get actual districts array

                                            if (Array.isArray(wards) && wards.length > 0) {
                                                $('#wards').empty().append('<option value="">Chọn thông tin</option>');

                                                $.each(wards, function(index, ward) {
                                                    $('#wards').append('<option value="' + ward.id + '">' + ward.name + '</option>');
                                                });

                                                $('#wards').trigger('change'); // Refresh Select2
                                            } else {
                                                console.warn("No districts found:", response);
                                                $('#wards').empty().append('<option value="">No wards available</option>');
                                                $('#wards').trigger('change');
                                            }
                                        }
                                    });
                                } else {
                                    // Clear the district select dropdown if no province is selected
                                    $('#ward').empty().append('<option value="">Chọn thông tin</option>');
                                }
                            });



                            // hide show input
                            // Event listener for select dropdown change
                            $("#tr_code_product, #tr_name_area,#tr_sonha, #tr_sothua, #tr_soto").fadeOut();
                            $("#product_type").change(function () {
                                var selectedValue = $(this).val(); // Lấy giá trị đã chọn

                                if (selectedValue === "517") {
                                    $("#tr_code_product, #tr_name_area").show(); // Hiển thị các input liên quan đến sản phẩm
                                    $("#tr_sonha, #tr_sothua, #tr_soto").hide(); // Ẩn các input không liên quan
                                } else if (selectedValue === "515") {
                                    $("#tr_code_product, #tr_name_area").hide(); // Ẩn các input sản phẩm
                                    $("#tr_sonha, #tr_sothua, #tr_soto").show(); // Hiển thị các input liên quan đến địa chỉ
                                } else {
                                    $("#tr_code_product, #tr_name_area, #tr_sonha, #tr_sothua, #tr_soto").hide(); // Ẩn tất cả các input khi không có lựa chọn nào phù hợp
                                }
                            });

                            // Event listener for select dropdown change
                            $("#tr_donvi_thoigiangia,#tr_donvi_thoigianthue,#tr_donvi_giathue, #tr_donvi_giaban ,#tr_giathue,#tr_thoigianthue, #tr_hethanthue, #tr_giaban, #tr_giachot").hide();
                            $form.find("#transaction_type").change(function () {
                                var selectedValue = $(this).val();
                                console.log(selectedValue);
                                if (selectedValue === "509") {
                                    $("#tr_donvi_giaban,#tr_giaban, #tr_giachot").show(); // Show input1 and input2
                                    $("#tr_donvi_thoigiangia,#tr_donvi_thoigianthue,#tr_donvi_giathue,#tr_giathue, #tr_thoigianthue, #tr_hethanthue").hide(); // Hide input3 and input4
                                } else if (selectedValue === "511") {
                                    $("#tr_donvi_giaban, #tr_giaban, #tr_giachot").hide();
                                    $("#tr_donvi_thoigiangia,#tr_donvi_thoigianthue,#tr_donvi_giathue, #tr_giathue, #tr_thoigianthue, #tr_hethanthue").show();
                                } else {
                                    $("#tr_donvi_thoigiangia,#tr_donvi_thoigianthue,#tr_donvi_giathue, #tr_donvi_giaban ,#tr_giathue,#tr_thoigianthue, #tr_hethanthue, #tr_giaban, #tr_giachot").hide();
                                }
                            });

                            // Event listener for select dropdown change
                            $("#tr_hoahong_sotien,#tr_hoahong_thang, #tr_hoahong_tyle").hide();
                            $form.find("#loaihoahong").change(function () {
                                var selectedValue = $(this).val();
                                console.log(selectedValue);
                                if (selectedValue === "651") {
                                    $("#tr_hoahong_tyle").show(); // Show input1 and input2
                                    $("#tr_hoahong_sotien, #tr_hoahong_thang").hide(); // Hide input3 and input4
                                } else if (selectedValue === "653") {
                                    $("#tr_hoahong_thang").show(); // Show input1 and input2
                                    $("#tr_hoahong_sotien, #tr_hoahong_tyle").hide(); // Hide input3 and input4
                                } else if (selectedValue === "649") {
                                    $("#tr_hoahong_sotien").show(); // Show input1 and input2
                                    $("#tr_hoahong_thang, #tr_hoahong_tyle").hide(); // Hide input3 and input4
                                }
                                else {
                                    $("#tr_hoahong_sotien,#tr_hoahong_thang, #tr_hoahong_tyle").hide();
                                }
                            });

//format number
                            $("input[name='thoigianthue'],input[name='dtd_ngang'], input[name='dtd_dai'],input[name='dtd_mathau'], input[name='dtd_congnhan'],input[name='dtqh_ngang'], input[name='dtqh_dai'], input[name='dtqh_mathau'], input[name='dtqh_xaydung'], input[name=''],input[name=''], input[name=''], input[name='ttk_duongrong'], input[name='ttk_dtsd'], input[name='dienthoaididong']").on("input", function(e) {
                                var value = $(this).val();
                                var sanitizedValue = value.replace(/[^0-9.]/g, '');
                                if ((sanitizedValue.match(/\./g) || []).length > 1) {
                                    sanitizedValue = sanitizedValue.replace(/\.+$/, '');
                                }
                                $(this).val(sanitizedValue);
                            });

                            // format a thousand separator
                            $("input[name='giathue'],input[name='giaban'],input[name='giachot']").on("input", function(e) {
                                var value = $(this).val();

                                // Remove any non-numeric characters except for a decimal point
                                var sanitizedValue = value.replace(/[^0-9.]/g, '');

                                // Ensure that only one decimal point is allowed
                                if ((sanitizedValue.match(/\./g) || []).length > 1) {
                                    sanitizedValue = sanitizedValue.replace(/\.+$/, '');  // Remove extra decimal points
                                }

                                // Format the number with comma as thousand separator
                                var parts = sanitizedValue.split('.');
                                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ','); // Add comma separator every three digits

                                // Combine the integer and decimal parts
                                sanitizedValue = parts.join('.');

                                // Update the input field with the sanitized and formatted value
                                $(this).val(sanitizedValue);
                            });




                        },
                        afterShowForm: function ($form) {

                        },
                        //$("#TblGrid_jqGrid tbody").append('<tr style="display: none;" class="FormData" id="tr_tablemain"><td class="CaptionTD"></td><td class="DataTD"><input type="text" role="textbox" id="tablemain" name="tablemain" value="'+selectedTable+'" rowid="" module="form" checkupdate="false" size="20" class="FormElement ui-widget-content ui-corner-all"></td></tr>')
                        onInitializeForm: function ($form) {
                            $form.css({height: "auto"});
                            $form.closest(".ui-jqdialog").css({height: "auto"});
                        },
                        viewPagerButtons:false, //hide next and pre in form
                        drag:true
                    });
                });

                function applySelect2Styles() {

                    $(".select2-container .select2-selection--single").css({
                        "height": "40px",  // Height of the select box
                        "line-height": "40px",  // Vertically align text
                        "border": "1px solid #ccc",  // Border color
                        "border-radius": "5px",  // Rounded corners
                        "font-size": "14px",  // Font size
                        "background-color": "#fff"  // Background color
                    });

                    $(".select2-container .select2-selection--single .select2-selection__rendered").css({
                        "padding-left": "10px",
                        "padding-right": "10px",
                        "line-height":"38px"
                    });

                    $(".select2-container--default .select2-results__options").css({
                        "max-height": "200px",  // Max height of dropdown options
                        "overflow-y": "auto"  // Enable vertical scrolling if the options exceed max height
                    });

                    $(".select2-container--default .select2-results__option").css({
                        "height": "30px",  // Height of individual options
                        "line-height": "30px"  // Align text vertically
                    });
                    $(".select2-container--default .select2-selection--single .select2-selection__arrow").css({
                        "height": "38px",  // Height of individual options
                        "width": "27px"  // Align text vertically
                    });

                    $(".select2-container--default .select2-results__option--highlighted").css({
                        "background-color": "#4CAF50",  // Highlight option on hover
                        "color": "white"  // White text on hover
                    });

                }

                // Enable filtering with select options for specific fields
                $("#jqGrid").jqGrid("filterToolbar", {
                    searchOperators: false, // Disable advanced operators
                    stringResult: true,
                    defaultSearch: "cn", // Default search contains (cn)
                    autosearch: true
                });

                // Apply styles for select filters
                $(".ui-search-toolbar select").css({
                    "height": "30px",
                    "border": "1px solid #ccc",
                    "border-radius": "4px",
                    "padding": "5px",
                    "text-align": "left",  // Ensures text inside dropdown is left-aligned
                    "display": "block",  // Ensures proper alignment
                    "width": "100%",  // Makes it match the column width
                });

                // add edit delete
                // Enable CRUD operations
                $("#jqGrid").navGrid("#jqGridPager", {
                    edit: true,
                    add: true,
                    del: true,
                    search: false,
                    refresh: true
                }, {
                    // Edit options
                    url: ajaxurl,
                    mtype: "POST",
                    editData: {
                        action: "edit_jqgrid_data",
                        table: selectedTable,
                        id: function () {
                            var selRowId = $("#jqGrid").jqGrid('getGridParam', 'selrow');
                            console.log("Sending ID:", selRowId);  // Debugging
                            return selRowId ? selRowId : 0;
                        }
                    },
                    beforeSubmit: function (postdata) {
                        // Remove commas before saving
                        postdata.giaban = postdata.giaban.replace(/,/g, ''); // Remove commas
                        postdata.giathue = postdata.giathue.replace(/,/g, ''); // Remove commas
                        postdata.giachot = postdata.giachot.replace(/,/g, ''); // Remove commas
                        return [true, '', postdata];
                    },
                    closeAfterEdit:true,
                    onClose: function () {
                        $(".select2-dropdown").hide();
                        return true;
                    },
                    width:1000,
                    beforeInitData: function () {
                        var selRowId = $grid.jqGrid('getGridParam', 'selrow');

                        if (!selRowId) {
                            alert("Please select a row before editing.");
                            return false; // Prevents further execution
                        }
                    },
                    recreateForm: true,
                    beforeShowForm: function ($form) {

                        $("#sData").click(function() {
                            var formValid = true;  // Flag to check if form is valid
                            var firstInvalidField = null;  // To store the first invalid field

                            // Loop through all input fields and select2 fields to check for emptiness or invalidity
                            $("form input, form select").each(function() {
                                // Check if the field is empty
                                if ($(this).val() === "" || $(this).hasClass('invalid')) {
                                    if (!firstInvalidField) {
                                        firstInvalidField = $(this);  // Store the first invalid field
                                    }
                                    formValid = false;  // Set the form as invalid
                                }

                                // For select2 fields, check if the select2 dropdown has an empty value
                                if ($(this).is("select") && $(this).hasClass("select2-hidden-accessible") && $(this).val() === null) {
                                    if (!firstInvalidField) {
                                        firstInvalidField = $(this);  // Store the first invalid select2 field
                                    }
                                    formValid = false;  // Set the form as invalid
                                }
                            });

                            // If form is invalid, scroll to the first invalid field
                            if (!formValid && firstInvalidField) {
                                // Check if the invalid field is a select2 field and scroll to its container
                                if (firstInvalidField.hasClass("select2-hidden-accessible")) {
                                    firstInvalidField = firstInvalidField.siblings(".select2-container");  // Get the select2 container
                                }

                                // Scroll to the field
                                $('html, body').animate({
                                    scrollTop: firstInvalidField.offset().top - 500  // Scroll to the field
                                }, 500); // 500 ms duration for scrolling
                            }

                            return formValid;  // Return the validation result (true if valid, false otherwise)
                        });

                        // Select the input field for the specific column (replace 'column_name' with your column identifier)




                        // fix template table
                        //=========================
                        var $table = $form.find("table");

                        // Apply table styles
                        $table.css({
                            "width": "100%",
                            "border-collapse": "collapse",
                            "display": "flex",
                            "flex-wrap": "wrap",
                            "gap": "34px",
                            "overflow": "hidden", // Ensures no extra width
                            'padding': '0px 0px 0px 30px',
                        });

                        // Ensure form rows align properly
                        $table.find("tbody").css({
                            "display": "flex",
                            "flex-wrap": "wrap",
                            "gap": "34px",
                            "width": "100%",
                            "overflow": "hidden"
                        });


                        // Style form-row properly
                        $(".form-row").css({
                            "display": "flex",
                            "justify-content": "space-between",
                            "width": "100%",
                            "gap": "15px",
                            "overflow": "hidden"
                        });


                        // Style each field container to not be too wide
                        $table.find("tr").css({
                            "display": "flex",
                            "flex-direction": "column",
                            "flex": "1",
                            "position":"relative",
                            "margin":"20px 0 20px 0px",
                            "max-width": "100%" // Ensures no overflow
                        });


                        // Get all table rows (tr) and exclude the first 2 rows from the grouping
                        var $rows = $table.find("tr"); // Select all table rows

// Remove all existing tbody elements (reset structure)
                        $table.find("tbody").remove();

// Re-create the first 2 rows without any grouping
                        var $newTbody = $("<tbody></tbody>").css({"width":"100%","height":"30px"}); // Make sure the first tbody has full width
                        $table.append($newTbody); // Append the new tbody to the table

// Add the first two rows directly to the new tbody
                        $newTbody.append($rows.slice(0, 2)); // Add first two rows as they are

// Create a new tbody for grouping after the first two rows
                        var $groupedTbody = $("<tbody></tbody>");
                        $table.append($groupedTbody); // Append the new tbody for grouped rows

// Now, group every 3 rows into the $groupedTbody (after first two)
                        $rows.slice(2).each(function (index) {
                            // Group every 3rd row starting from the 3rd one
                            if (index % 4 === 0 && index !== 0) {
                                $groupedTbody = $("<tbody></tbody>"); // Create a new tbody for every group
                                $table.append($groupedTbody); // Append a new tbody for each group of 3
                            }

                            // Append the current row to the grouped tbody
                            $groupedTbody.append($(this));
                        });

// Reinitialize Select2 on the select elements after DOM manipulation


//==========================



                        //select2
                        $form.find("select").each(function () {
                            $(this).select2({
                                width: '100%',
                                placeholder: 'Chọn thông tin',
                                allowClear: false,
                                minimumResultsForSearch: 0 // Enable search functionality
                            })
                        });

                        // Apply Select2 styling
                        applySelect2Styles();


                        /* var $thoigianthue = $('input[name="thoigianthue"]');
                         if ($thoigianthue.length > 0) {
                         $thoigianthue.datepicker({
                         dateFormat: 'yy-mm-dd',
                         changeMonth: true,
                         changeYear: true
                         });
                         };*/
                        var $hethanthue = $('input[name="hethanthue"]');
                        if ($hethanthue.length > 0) {
                            $hethanthue.datepicker({
                                dateFormat: 'yy-mm-dd',
                                changeMonth: true,
                                changeYear: true
                            });
                        }




                        $form.addClass("custom-jqgrid-form");
                        // Modify the form header style
                        $form.parent().find('.ui-jqdialog-titlebar').css({
                            'background': '#FFA500', // Orange background
                            'color': 'white',
                            'font-size': '16px',
                            'font-weight': 'bold',
                            'padding': '10px',
                            'border-radius': '8px 8px 0 0'
                        });

                        // Style input fields
                        $form.find('input, select, textarea').css({
                            'width': '100%',
                            'padding': '8px',
                            'border': '1px solid #ccc',
                            'border-radius': '5px',
                            'font-size': '13px',
                        });

                        // Style the form buttons
                        $form.parent().find('.ui-jqdialog-buttonset button').css({
                            'background': '#4CAF50',
                            'color': 'white',
                            'font-size': '14px',
                            'padding': '8px 12px',
                            'border-radius': '5px',
                            'border': 'none',
                            'cursor': 'pointer',
                            'transition': '0.3s'
                        }).hover(
                            function () {
                                $(this).css('background', '#388E3C');
                            },
                            function () {
                                $(this).css('background', '#4CAF50');
                            }
                        );

                        // Change title text
                        $(".ui-jqdialog-title").html('<img src="https://s.w.org/images/core/emoji/15.0.3/svg/2795.svg" style="width: 18px; height: 18px; filter: invert(40%) sepia(90%) saturate(3000%) hue-rotate(90deg);"> Thêm Mới');

                        // Apply modern styling to the header
                        $(".ui-jqdialog-titlebar").css({
                            "color": "#ffa500",          // White text
                            "font-size": "18px",
                            "font-weight": "600",
                            "padding": "5px",
                            "border-radius": "2px 2px 0 0",
                            "border-bottom": "3px solid #4caf50" // Darker green border bottom
                        });

                        // Style the close button for better UX
                        $(".ui-jqdialog-titlebar-close").css({
                            "background": "transparent",
                            "border": "none",
                            "cursor": "pointer",
                            "padding": "4px",
                            "transition": "0.3s"
                        });

                        // Hover effect for close button
                        $(".ui-jqdialog-titlebar-close span").css({
                            "color": "#fff",
                            "font-size": "16px"
                        });

                        $(".ui-jqdialog-titlebar-close").hover(
                            function () { $(this).css("opacity", "0.7"); },
                            function () { $(this).css("opacity", "1"); }
                        );


                        var dlgDiv = $("#editmod" + $grid[0].id);
                        var parentDiv = dlgDiv.parent();
                        var dlgWidth = dlgDiv.width();
                        var parentWidth = parentDiv.width();

                        // Đặt modal lên sát mép trên màn hình
                        dlgDiv.css({
                            "top": "-70px",  // Đẩy modal lên sát viền trên màn hình
                            "left": Math.round((parentWidth - dlgWidth) / 2) + "px",
                            /* "z-index": "9999", // Luôn nằm trên các phần tử khác
                             "position": "fixed" // Giữ nguyên vị trí khi cuộn trang*/
                        });



                        // Fix the main dialog to avoid horizontal scrolling
                        var $dialog = $form.closest(".ui-jqdialog");
                        $dialog.css({
                            "border-radius": "10px",
                            "background": "#fff",
                            "box-shadow": "0px 4px 10px rgba(0,0,0,0.2)",
                            //"max-width": "800px",
                            "width": "70%",  // Prevents form from being too wide
                            "overflow": "hidden", // Prevents horizontal scrolling,
                            "padding" :"0px",
                        });

                        // Style labels
                        $form.find("td.CaptionTD").css({
                            "font-weight": "bold",
                            "color": "#333",
                            //"width": "100%",
                            "margin-bottom": "5px",
                            "text-align": "left",
                            "position":"absolute",
                            "top": "-10px",
                            "z-index": "1",
                            "left": "8px",
                            "height": "20px",
                            "background" : "linear-gradient(to bottom, transparent 50%, rgb(255, 255, 255) 50%, rgb(255, 255, 255) 51%, transparent 65%)",

                        });

                        // Style input fields
                        $form.find("td.DataTD input, td.DataTD select").css({
                            "width": "100%",
                            "padding": "8px",
                            "border": "1px solid #ccc",
                            "border-radius": "5px",
                            "font-size": "14px",
                            "box-sizing": "border-box",
                            "height": "40px",
                            "transition": "all 0.3s ease-in-out" // Hiệu ứng mượt khi thay đổi trạng thái
                        });

                        // Hover effect
                        $form.find("td.DataTD input, td.DataTD select").hover(
                            function () {
                                $(this).css("border-color", "#007bff"); // Khi hover, viền đổi màu xanh
                            },
                            function () {
                                $(this).css("border-color", "#ccc"); // Khi không hover, trở lại màu cũ
                            }
                        );

                        // Focus effect
                        $form.find("td.DataTD input, td.DataTD select").on("focus", function () {
                            $(this).css({
                                "border-color": "#007bff",
                                "box-shadow": "0 0 5px rgba(0, 123, 255, 0.5)"
                            });
                        }).on("blur", function () {
                            $(this).css({
                                "border-color": "#ccc",
                                "box-shadow": "none"
                            });
                        });

                        // Fix button alignment
                        $(".EditButton").css({
                            "display": "flex",
                            "justify-content": "center",
                            "gap": "15px",
                            "margin-top": "20px"
                        });

                        // Style Submit button
                        $("#sData").html('<span style="color: #fff;">✔ Tiếp Tục</span>').css({
                            "background": "#4CAF50",
                            "border-radius": "5px",
                            "padding": "10px 20px",
                            "font-weight": "bold",
                            "box-shadow": "0 2px 4px rgba(0,0,0,0.2)",
                            "border": "none"
                        });

                        // Style Cancel button
                        $("#cData").html('<span style="color: #fff;">✖ Hủy</span>').css({
                            "background": "#d9534f",
                            "border-radius": "5px",
                            "padding": "10px 20px",
                            "font-weight": "bold",
                            "box-shadow": "0 2px 4px rgba(0,0,0,0.2)",
                            "border": "none"
                        });



                        // Hide form error message
                        $("#FormError").hide();
                        $('[value="_empty"]').hide();

                        // handle ajax province, district, wards
                        $('#province').on('change', function() {
                            $('#district').prop('disabled', true);  // Disable the district dropdown
                            $('#district').empty().append('<option value="">Tải thông tin</option>');
                            var province_id = $(this).val(); // Get selected province ID
                            if (province_id) {
                                // If a province is selected, make an AJAX request
                                $.ajax({
                                    url: ajaxurl, // WordPress AJAX URL
                                    method: 'POST',
                                    data: {
                                        action: 'load_districts', // Custom action to load districts
                                        province_id: province_id
                                    },
                                    success: function(response) {
                                        // console.log(response); // Check the response in the browser console to see if it has the data
                                        $('#district').prop('disabled', false);  // Enable the district dropdown
                                        // Empty the district dropdown first
                                        $('#district').empty().append('<option value="">Chọn thông tin</option>');
                                        var districts = response.data.data || []; // Get actual districts array

                                        if (Array.isArray(districts) && districts.length > 0) {
                                            $('#district').empty().append('<option value="">Chọn thông tin</option>');

                                            $.each(districts, function(index, district) {
                                                $('#district').append('<option value="' + district.id + '">' + district.name + '</option>');
                                            });

                                            $('#district').trigger('change'); // Refresh Select2
                                        } else {
                                            console.warn("No districts found:", response);
                                            $('#district').empty().append('<option value="">No districts available</option>');
                                            $('#district').trigger('change');
                                        }
                                    }
                                });
                            } else {
                                // Clear the district select dropdown if no province is selected
                                $('#district').empty().append('<option value="">Chọn thông tin</option>');
                            }
                        });

                        $('#district').on('change', function() {
                            $('#wards').prop('disabled', true);  // Disable the district dropdown
                            $('#wards').empty().append('<option value="">Tải thông tin</option>');
                            var district_id = $(this).val(); // Get selected district ID
                            if (district_id) {
                                // If a district is selected, make an AJAX request
                                $.ajax({
                                    url: ajaxurl, // WordPress AJAX URL
                                    method: 'POST',
                                    data: {
                                        action: 'load_wards', // Custom action to load districts
                                        district_id: district_id
                                    },
                                    success: function(response) {
                                        // console.log(response); // Check the response in the browser console to see if it has the data
                                        $('#wards').prop('disabled', false);  // Enable the district dropdown
                                        // Empty the district dropdown first
                                        $('#wards').empty().append('<option value="">Chọn thông tin</option>');
                                        var wards = response.data.data || []; // Get actual districts array

                                        if (Array.isArray(wards) && wards.length > 0) {
                                            $('#wards').empty().append('<option value="">Chọn thông tin</option>');

                                            $.each(wards, function(index, ward) {
                                                $('#wards').append('<option value="' + ward.id + '">' + ward.name + '</option>');
                                            });

                                            $('#wards').trigger('change'); // Refresh Select2
                                        } else {
                                            console.warn("No districts found:", response);
                                            $('#wards').empty().append('<option value="">No wards available</option>');
                                            $('#wards').trigger('change');
                                        }
                                    }
                                });
                            } else {
                                // Clear the district select dropdown if no province is selected
                                $('#ward').empty().append('<option value="">Chọn thông tin</option>');
                            }
                        });



                        // hide show input
                        // Event listener for select dropdown change
                        $("#tr_code_product, #tr_name_area,#tr_sonha, #tr_sothua, #tr_soto").fadeOut();
                        $("#product_type").change(function () {
                            var selectedValue = $(this).val(); // Lấy giá trị đã chọn

                            if (selectedValue === "517") {
                                $("#tr_code_product, #tr_name_area").show(); // Hiển thị các input liên quan đến sản phẩm
                                $("#tr_sonha, #tr_sothua, #tr_soto").hide(); // Ẩn các input không liên quan
                            } else if (selectedValue === "515") {
                                $("#tr_code_product, #tr_name_area").hide(); // Ẩn các input sản phẩm
                                $("#tr_sonha, #tr_sothua, #tr_soto").show(); // Hiển thị các input liên quan đến địa chỉ
                            } else {
                                $("#tr_code_product, #tr_name_area, #tr_sonha, #tr_sothua, #tr_soto").hide(); // Ẩn tất cả các input khi không có lựa chọn nào phù hợp
                            }
                        });

                        // Event listener for select dropdown change
                        $("#tr_donvi_thoigiangia,#tr_donvi_thoigianthue,#tr_donvi_giathue, #tr_donvi_giaban ,#tr_giathue,#tr_thoigianthue, #tr_hethanthue, #tr_giaban, #tr_giachot").hide();
                        $form.find("#transaction_type").change(function () {
                            var selectedValue = $(this).val();
                            console.log(selectedValue);
                            if (selectedValue === "509") {
                                $("#tr_donvi_giaban,#tr_giaban, #tr_giachot").show(); // Show input1 and input2
                                $("#tr_donvi_thoigiangia,#tr_donvi_thoigianthue,#tr_donvi_giathue,#tr_giathue, #tr_thoigianthue, #tr_hethanthue").hide(); // Hide input3 and input4
                            } else if (selectedValue === "511") {
                                $("#tr_donvi_giaban, #tr_giaban, #tr_giachot").hide();
                                $("#tr_donvi_thoigiangia,#tr_donvi_thoigianthue,#tr_donvi_giathue, #tr_giathue, #tr_thoigianthue, #tr_hethanthue").show();
                            } else {
                                $("#tr_donvi_thoigiangia,#tr_donvi_thoigianthue,#tr_donvi_giathue, #tr_donvi_giaban ,#tr_giathue,#tr_thoigianthue, #tr_hethanthue, #tr_giaban, #tr_giachot").hide();
                            }
                        });

                        // Event listener for select dropdown change
                        $("#tr_hoahong_sotien,#tr_hoahong_thang, #tr_hoahong_tyle").hide();
                        $form.find("#loaihoahong").change(function () {
                            var selectedValue = $(this).val();
                            console.log(selectedValue);
                            if (selectedValue === "651") {
                                $("#tr_hoahong_tyle").show(); // Show input1 and input2
                                $("#tr_hoahong_sotien, #tr_hoahong_thang").hide(); // Hide input3 and input4
                            } else if (selectedValue === "653") {
                                $("#tr_hoahong_thang").show(); // Show input1 and input2
                                $("#tr_hoahong_sotien, #tr_hoahong_tyle").hide(); // Hide input3 and input4
                            } else if (selectedValue === "649") {
                                $("#tr_hoahong_sotien").show(); // Show input1 and input2
                                $("#tr_hoahong_thang, #tr_hoahong_tyle").hide(); // Hide input3 and input4
                            }
                            else {
                                $("#tr_hoahong_sotien,#tr_hoahong_thang, #tr_hoahong_tyle").hide();
                            }
                        });

//format number
                        $("input[name='thoigianthue'],input[name='dtd_ngang'], input[name='dtd_dai'],input[name='dtd_mathau'], input[name='dtd_congnhan'],input[name='dtqh_ngang'], input[name='dtqh_dai'], input[name='dtqh_mathau'], input[name='dtqh_xaydung'], input[name=''],input[name=''], input[name=''], input[name='ttk_duongrong'], input[name='ttk_dtsd'], input[name='dienthoaididong']").on("input", function(e) {
                            var value = $(this).val();
                            var sanitizedValue = value.replace(/[^0-9.]/g, '');
                            if ((sanitizedValue.match(/\./g) || []).length > 1) {
                                sanitizedValue = sanitizedValue.replace(/\.+$/, '');
                            }
                            $(this).val(sanitizedValue);
                        });

                        // format a thousand separator
                        $("input[name='giathue'],input[name='giaban'],input[name='giachot']").on("input", function(e) {
                            var value = $(this).val();

                            // Remove any non-numeric characters except for a decimal point
                            var sanitizedValue = value.replace(/[^0-9.]/g, '');

                            // Ensure that only one decimal point is allowed
                            if ((sanitizedValue.match(/\./g) || []).length > 1) {
                                sanitizedValue = sanitizedValue.replace(/\.+$/, '');  // Remove extra decimal points
                            }

                            // Format the number with comma as thousand separator
                            var parts = sanitizedValue.split('.');
                            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ','); // Add comma separator every three digits

                            // Combine the integer and decimal parts
                            sanitizedValue = parts.join('.');

                            // Update the input field with the sanitized and formatted value
                            $(this).val(sanitizedValue);
                        });




                    },
                    onInitializeForm: function ($form) {
                        $form.css({height: "auto"});
                        $form.closest(".ui-jqdialog").css({height: "auto"});
                    },
                    viewPagerButtons:false, //hide next and pre in form
                    drag:true
                }, {
                    // Add options
                    url: ajaxurl,
                    mtype: "POST",
                    editData: { action: "add_jqgrid_data", table: selectedTable},
                    closeAfterAdd: true,
                    reloadAfterSubmit: true,
                    onClose: function () {
                        $(".select2-dropdown").hide();
                        return true;
                    },
                    width:1000,
                    beforeInitData: function () {
                        selRowId = $grid.jqGrid('getGridParam', 'selrow');
                    },
                    recreateForm: true,
                    beforeShowForm: function ($form) {


                        // remove US$ pap status
                        /* if(jq_table == 'papmall_pap'){
                         var priceOrigin = $("#pap_price").val();
                         var priceCut = priceOrigin.split("US$ ");
                         $("#pap_price").val(parseFloat(priceCut[1]));
                         }*/
                        /*var dlgDiv = $("#editmod" + $grid[0].id);
                         var parentDiv = dlgDiv.parent();
                         var dlgWidth = dlgDiv.width();
                         var parentWidth = parentDiv.width();
                         var dlgHeight = dlgDiv.height();
                         var parentHeight = parentDiv.height();
                         dlgDiv[0].style.top = Math.round((parentHeight-dlgHeight)/2) + "px";
                         dlgDiv[0].style.left = Math.round((parentWidth-dlgWidth)) + "px";*/
                        // Add custom class for styling
                        $form.addClass("custom-jqgrid-form");

                        // Modify the form header style
                        $form.parent().find('.ui-jqdialog-titlebar').css({
                            'background': '#FFA500', // Orange background
                            'color': 'white',
                            'font-size': '16px',
                            'font-weight': 'bold',
                            'padding': '10px',
                            'border-radius': '8px 8px 0 0'
                        });

                        // Style input fields
                        $form.find('input, select, textarea').css({
                            'width': '100%',
                            'padding': '8px',
                            'border': '1px solid #ccc',
                            'border-radius': '5px',
                        });

                        // Style the form buttons
                        $form.parent().find('.ui-jqdialog-buttonset button').css({
                            'background': '#4CAF50',
                            'color': 'white',
                            'font-size': '14px',
                            'padding': '8px 12px',
                            'border-radius': '5px',
                            'border': 'none',
                            'cursor': 'pointer',
                            'transition': '0.3s'
                        }).hover(
                            function () {
                                $(this).css('background', '#388E3C');
                            },
                            function () {
                                $(this).css('background', '#4CAF50');
                            }
                        );

                        // Change title text
                        $(".ui-jqdialog-title").html('<img src="https://s.w.org/images/core/emoji/15.0.3/svg/2795.svg" style="width: 18px; height: 18px; filter: invert(40%) sepia(90%) saturate(3000%) hue-rotate(90deg);"> Thêm Mới');

                        // Apply modern styling to the header
                        $(".ui-jqdialog-titlebar").css({
                            "color": "#ffa500",          // White text
                            "font-size": "18px",
                            "font-weight": "600",
                            "padding": "5px",
                            "border-radius": "2px 2px 0 0",
                            "border-bottom": "3px solid #4caf50" // Darker green border bottom
                        });

                        // Style the close button for better UX
                        $(".ui-jqdialog-titlebar-close").css({
                            "background": "transparent",
                            "border": "none",
                            "cursor": "pointer",
                            "padding": "4px",
                            "transition": "0.3s"
                        });

                        // Hover effect for close button
                        $(".ui-jqdialog-titlebar-close span").css({
                            "color": "#fff",
                            "font-size": "16px"
                        });

                        $(".ui-jqdialog-titlebar-close").hover(
                            function () { $(this).css("opacity", "0.7"); },
                            function () { $(this).css("opacity", "1"); }
                        );

                        setTimeout(function () {
                            $("#sData .fm-button-text").text("Lưu");  // Change "Submit" to "Lưu"
                            $("#cData .fm-button-text").text("Hủy bỏ");  // Change "Cancel" to "Hủy bỏ"
                        }, 100); // Ensure the form is fully rendered



                        var dlgDiv = $("#editmod" + $grid[0].id);
                        var parentDiv = dlgDiv.parent();
                        var dlgWidth = dlgDiv.width();
                        var parentWidth = parentDiv.width();

                        // Đặt modal lên sát mép trên màn hình
                        dlgDiv.css({
                            "top": "-50px",  // Đẩy modal lên sát viền trên màn hình
                            "left": Math.round((parentWidth - dlgWidth) / 2) + "px",
                            "z-index": "9999", // Luôn nằm trên các phần tử khác
                            "position": "fixed" // Giữ nguyên vị trí khi cuộn trang
                        });


                        //$("#TblGrid_jqGrid tbody").append('<tr style="display: none;" class="FormData" id="tr_tablemain"><td class="CaptionTD"></td><td class="DataTD"><input type="text" role="textbox" id="tablemain" name="tablemain" value="'+selectedTable+'" rowid="" module="form" checkupdate="false" size="20" class="FormElement ui-widget-content ui-corner-all"></td></tr>');
                    },

                        //$("#TblGrid_jqGrid tbody").append('<tr style="display: none;" class="FormData" id="tr_tablemain"><td class="CaptionTD"></td><td class="DataTD"><input type="text" role="textbox" id="tablemain" name="tablemain" value="'+selectedTable+'" rowid="" module="form" checkupdate="false" size="20" class="FormElement ui-widget-content ui-corner-all"></td></tr>')
                    onInitializeForm: function ($form) {
                        $form.css({height: "auto"});
                        $form.closest(".ui-jqdialog").css({height: "auto"});
                    },
                    viewPagerButtons:false, //hide next and pre in form
                    drag:true
                }, {
                        // Delete options
                        url: ajaxurl, // Ensure this is the correct AJAX URL
                        mtype: "POST", // POST method for the request
                        beforeSubmit: function (postdata) {
                            // Get the selected row ID
                            var selRowId = $("#jqGrid").jqGrid('getGridParam', 'selrow');
                            console.log("Selected Row ID:", selRowId); // Debugging
                            console.log("table:", selectedTable); // Debugging

                            // If no row is selected, alert and prevent submission
                            if (!selRowId) {
                                alert("Please select a row before deleting.");
                                return [false, "No row selected."]; // Prevent submission if no row is selected
                            }

                            // Attach the selected row ID to the postdata before sending it
                            postdata.id = selRowId;

                            // Continue with the submission
                            return [true, ""];
                        },
                        editData: {
                            action: "delete_jqgrid_data",  // The WordPress action hook
                            table: selectedTable,          // Your table name (ensure this is set properly)
                            // No need to handle ID in this part anymore since it's already handled in beforeSubmit
                        },
                        reloadAfterSubmit: true,  // Reload grid after successful deletion
                    }


                );

            },
            error: function () {
                console.error("Failed to fetch colModel");
            }
        });
    }
});