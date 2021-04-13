/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    "jquery",
    'Magento_Ui/js/modal/confirm',
    'uiComponent',
    'swalPopup',
    'mage/validation'
], function($, confirmation, Component) {

    'use strict';
    var $ = jQuery;

    return Component.extend({
        defaults: {
            action: ''
        },
        Baseurl: null,
        /**
         * @override
         */
        initialize: function(config) {
            var self = this;
            this._clickSuccess();
            this._deleteSuccess();
            this.Baseurl = config.action;
            console.log(this.Baseurl);
            console.log($('body')); 
            return this._super();
        },
        MyCustomFunction: function() {
            return "my function";
        },
        /**
         * @return array  
         */
        getFormData: function(formElem) {
            var unindexed_array = formElem.serializeArray();
            var indexed_array = {};
            jQuery.map(unindexed_array, function(n, i) {
                indexed_array[n['name']] = n['value'];
            });
            return indexed_array;
        },
        /***
         * $delete order
         */
        _deleteSuccess: function() {
            var that = this;
            $(document).on("click", '#acs-widget-delete', function(e) {
                var id = $(this).attr('data-id');
                var voucher = $(this).attr('data-voucher');
                confirmation({
                    title: 'Delete Voucher',
                    content: 'Do you want to delete this order voucher?',
                    actions: {
                        confirm: function() {

                            console.log(id);
                            console.log(voucher);
                            $.ajax({
                                showLoader: true,
                                url: that.Baseurl + 'delete',
                                data: { id: id, voucherId: voucher },
                                type: "POST",
                                success: function(data) {
                                    console.log(data);
                                    if (data.success) {
                                        var message = data.message ? data.message : 'Acs deleted successfully....!';
                                        swal(
                                            'Success',
                                            message,
                                            'success'
                                        );
                                        setTimeout(function(){   window.location.reload(); }, 3000);
                                    } else {
                                        console.log(data)
                                        var message = data.message ? data.message : 'YOU MUST FIRST GO AND DELETE THE VOUCHER FROM THE SHIPPING LIST!';                                        
                                        swal(
                                            'Error!',
                                            message,
                                            'error'
                                        )
                                    }

                                },
                                error: function(error) {
                                    console.log(error)
                                    swal(
                                        'Error!',
                                        ' Order shipping voucher is not deleted. Please try again!',
                                        'error'
                                    )
                                }
                            });
                        },
                        cancel: function() {
                            return false;
                        }
                    }

                });
            });
        },

        /**
         * @create voucher
         */
        _clickSuccess: function() {
            var that = this;

            $(document).on("click", '#acs-widget-orders', function(e) {
                var dataForm = $('#acs-widget-orders-and-returns-form');
                var ignore = null;
                var data = [];
                $("#additional_serv input:checked").each(function() {
                    if ($('#amount').val() < 0 && $(this).val() != "COD")
                        data.push($(this).val());
                    else {
                        data.push($(this).val());
                    }
                });

                dataForm.mage('validation', {
                    ignore: ignore ? ':hidden:not(' + ignore + ')' : ':hidden'
                }).find('input:text').attr('autocomplete', 'off');

                if (dataForm.validation('isValid')) {
                    confirmation({
                        title: 'Create Voucher',
                        content: 'Do you want to create this user voucher?',
                        actions: {
                            confirm: function() {
                                var formData = that.getFormData($("#acs-widget-orders-and-returns-form"));
                                formData["Acs_Delivery_Products_set"] = data;

                                $.ajax({
                                    showLoader: true,
                                    url: that.Baseurl + 'index',
                                    data: formData,
                                    type: "POST",
                                    success: function(data) {
                                        if (data.success) {
                                            var message =  data.message ? data.message:'Order shipping created successfully...!';
                                              swal({
                                                title: "Success job", 
                                                text: message,
                                                type: "success"
                                            })
                                                if($('button#order_ship').length > 0){
                                                  $('button#order_ship').trigger('click');
                                                }else{
                                                    setTimeout(function(){   window.location.reload(); }, 3000);
                                                }
                                           
                                        } else {
                                            console.log(data)
                                            var message =  data.message ? data.message:'Order shipping created successfully...!';                                            
                                            swal(
                                                'Error!',
                                                message,
                                                'error'
                                            )
                                        }


                                    },
                                    error: function(error) {
                                        console.log(error)
                                        swal(
                                            'Error!',
                                            error,
                                            'error'
                                        )
                                    }
                                });
                            },

                            cancel: function() {
                                return false;
                            }
                        }
                    });
                }
            })

        },
    });

});

//     "use strict";

//     return Component.extend({
//         initialize: function() {
//             console.log(this);
//             console.log($(this));
//             console.log('sgfdasfdas');
//             this._super();
//             this._clickSuccess();
//         },
//         /** Initialize observable properties */
//         initObservable: function() {


//         },
//         _clickSuccess: function() {
//             $(document).on("click", '#acs-widget-orders', function(e) {
//                 console.log('popopopopop')
//                 confirmation({
//                     title: 'Accept user',
//                     content: 'Do you want to accept this user?',
//                     actions: {
//                         confirm: function() {
//                             console.log('popopop');
//                             // $.ajax({
//                             //      showLoader: true,
//                             //      url: 'route-for-controller',
//                             //      data: {},
//                             //      type: "POST",
//                             //      success: function (data) {
//                             //              console.log(data);

//                             //         },
//                             //      error: function (error) {

//                             //                  }
//                             // });
//                         },

//                         cancel: function() {
//                             return false;
//                         }
//                     }
//                 });
//                 console.log('opopopo')
//             })

//         },
//         /**
//          * Validate feedback form
//          */
//         validateForm: function() {

//         },
//         submitFeedback: function() {

//         },
//         totalfeeds: function() {


//         },
//         deleteFeeds: function(parent, data) {

//         },

//     });
// });