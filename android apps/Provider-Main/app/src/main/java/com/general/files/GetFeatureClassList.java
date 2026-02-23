package com.general.files;

import com.buddyverse.providers.BuildConfig;
import com.utils.CommonUtilities;
import com.utils.Logger;
import com.utils.Utils;

import java.lang.reflect.Field;
import java.util.ArrayList;
import java.util.HashMap;

public class GetFeatureClassList {

    private static final String actPath = "com.act.";
    private static final String resourceFilePath = "res/layout/";
    private static final String resourcePath = "layout";

    public static HashMap<String, String> getAllGeneralClasses() {
        HashMap<String, String> classParams = new HashMap<>();

        ArrayList<String> safetyToolsClassList = new ArrayList<>();
        safetyToolsClassList.add("com.general.features.SafetyTools");
        safetyToolsClassList.add(resourceFilePath + "dialog_safety_tools");
        safetyToolsClassList.add(resourceFilePath + "dialog_safety_tools_audio_recording");
        safetyToolsClassList.add(resourceFilePath + "dialog_safety_tools_audio_recording_available");
        classParams.put("SAFETY_TOOLS_MODULE", "No");
        for (String item : safetyToolsClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("SAFETY_TOOLS_MODULE", "Yes");
                break;
            }
        }

        ArrayList<String> biddingClassList = new ArrayList<>();
        biddingClassList.add(actPath + "BiddingViewDetailsActivity");
        biddingClassList.add(resourceFilePath + "activity_bidding_view_details");
        biddingClassList.add("com.fragments.BiddingBookingFragment");
        biddingClassList.add(resourceFilePath + "fragment_biding");
        biddingClassList.add(actPath + "BiddingCategoryActivity");
        biddingClassList.add(resourceFilePath + "activity_bidding_category");
        biddingClassList.add("com.adapter.files.PinnedBiddingServicesListAdapter");
        biddingClassList.add(resourceFilePath + "item_bidding_category_list");
        biddingClassList.add("com.adapter.files.BiddingListRecycleAdapter");
        biddingClassList.add(resourceFilePath + "item_bidding_layout");
        biddingClassList.add(actPath + "BiddingHistoryDetailActivity");
        biddingClassList.add(resourceFilePath + "activity_bidding_history_detail");
        biddingClassList.add(resourceFilePath + "dialog_bidding_re_offre");

        biddingClassList.add(actPath + "BiddingAdditionalMediaActivity");
        biddingClassList.add(resourceFilePath + "activity_bidding_media");
        biddingClassList.add("com.adapter.files.BidAdditionalMediaAdapter");
        biddingClassList.add(resourceFilePath + "item_bid_additional_media");

        classParams.put("BID_SERVICE", "No");
        for (String item : biddingClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("BID_SERVICE", "Yes");
                break;
            }
        }

        ArrayList<String> voipServiceClassList = new ArrayList<>();
        voipServiceClassList.add("com.general.call.LocalHandler");
        voipServiceClassList.add("com.general.call.SinchHandler");
        voipServiceClassList.add("com.general.call.TwilioHandler");
        voipServiceClassList.add("com.general.call.AppRTCAudioManager");
        voipServiceClassList.add("com.general.call.CameraCapturerCompat");
        voipServiceClassList.add("com.general.call.V3CallScreen");
        voipServiceClassList.add("com.general.call.V3CallListener");
        voipServiceClassList.add(resourceFilePath + "v3_call_screen");

        classParams.put("VOIP_SERVICE", "No");
        for (String item : voipServiceClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("VOIP_SERVICE", "Yes");
                break;
            }
        }

        ArrayList<String> advertisementClassList = new ArrayList<>();
        advertisementClassList.add("com.general.files.OpenAdvertisementDialog");
        advertisementClassList.add(resourceFilePath + "advertisement_dailog");

        classParams.put("ADVERTISEMENT_MODULE", "No");
        for (String item : advertisementClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("ADVERTISEMENT_MODULE", "Yes");
                break;
            }
        }

        ArrayList<String> linkedInClassList = new ArrayList<>();
        linkedInClassList.add("com.general.files.OpenLinkedinDialog");
        linkedInClassList.add("com.general.files.RegisterLinkedinLoginResCallBack");

        classParams.put("LINKEDIN_MODULE", "No");
        for (String item : linkedInClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("LINKEDIN_MODULE", "Yes");
                break;
            }
        }

        ArrayList<String> cardIOClassList = new ArrayList<>();
        cardIOClassList.add("io.card.payment.CardIOActivity");

        classParams.put("CARD_IO", "No");
        for (String item : cardIOClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("CARD_IO", "Yes");
                break;
            }
        }

        ArrayList<String> liveChatClassList = new ArrayList<>();
        liveChatClassList.add("com.livechatinc.inappchat.ChatWindowActivity");

        classParams.put("LIVE_CHAT", "No");
        for (String item : liveChatClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("LIVE_CHAT", "Yes");
                break;
            }
        }

        ArrayList<String> wayBillClassList = new ArrayList<>();
        wayBillClassList.add(actPath + "WayBillActivity");
        wayBillClassList.add(resourceFilePath + "activity_way_bill");

        classParams.put("WAYBILL_MODULE", "No");
        for (String item : wayBillClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("WAYBILL_MODULE", "Yes");
                break;
            }
        }


        ArrayList<String> deliverAllClassList = new ArrayList<>();
        deliverAllClassList.add(actPath + "deliverAll.DeliverAllCabRequestedActivity");
        deliverAllClassList.add(resourceFilePath + "activity_deliver_all_cab_requested");
        deliverAllClassList.add(actPath + "deliverAll.DeliverAllRatingActivity");
        deliverAllClassList.add(resourceFilePath + "activity_deliver_all_rating");
        deliverAllClassList.add(actPath + "deliverAll.LiveTaskListActivity");
        deliverAllClassList.add(resourceFilePath + "activity_live_tasks");
        deliverAllClassList.add(actPath + "deliverAll.LiveTrackOrderDetailActivity");
        deliverAllClassList.add(resourceFilePath + "activity_live_track_order_detail");
        deliverAllClassList.add(actPath + "deliverAll.LiveTrackOrderDetail2Activity");
        deliverAllClassList.add(resourceFilePath + "activity_live_track_order_new_detail");
        deliverAllClassList.add(actPath + "deliverAll.UserPrefrenceActivity");
        deliverAllClassList.add(resourceFilePath + "activity_user_prefrence");
        deliverAllClassList.add(actPath + "deliverAll.OrderDetailsActivity");
        deliverAllClassList.add(resourceFilePath + "activity_order_details");
        deliverAllClassList.add(actPath + "deliverAll.OrderHistoryActivity");
        deliverAllClassList.add(resourceFilePath + "activity_order_history");
        deliverAllClassList.add(actPath + "deliverAll.TrackOrderActivity");
        deliverAllClassList.add(resourceFilePath + "activity_track_driver_location");
        deliverAllClassList.add("com.model.deliverAll.liveTaskListDataModel");
        deliverAllClassList.add("com.model.deliverAll.orderDetailDataModel");
        deliverAllClassList.add("com.model.deliverAll.orderItemDetailDataModel");
        deliverAllClassList.add("com.adapter.files.deliverAll.OrderHistoryRecycleAdapter");
        deliverAllClassList.add(resourceFilePath + "item_order_history_header_design");
        deliverAllClassList.add(resourceFilePath + "item_order_history_design");
        deliverAllClassList.add("com.adapter.files.deliverAll.OrderItemListRecycleAdapter");
        deliverAllClassList.add(resourceFilePath + "order_item_list_cell");
        deliverAllClassList.add("com.adapter.files.deliverAll.OrderListRecycleAdapter");
        deliverAllClassList.add(resourceFilePath + "live_task_order_list_cell");
        deliverAllClassList.add("com.adapter.files.deliverAll.VehicleSingleCheckListAdapter");
        deliverAllClassList.add(resourceFilePath + "item_select_service_deliver_all_design");

        classParams.put("DELIVER_ALL", "No");
        for (String item : deliverAllClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("DELIVER_ALL", "Yes");
                break;
            }
        }


        //GiftCard Module
        ArrayList<String> giftCardClassList = new ArrayList<>();
        giftCardClassList.add(actPath + "giftcard.adapter.GiftCardImagePagerAdapter");
        giftCardClassList.add(resourceFilePath + "item_giftcard");
        giftCardClassList.add(actPath + "giftcard.GiftCardRedeemActivity");
        giftCardClassList.add(resourceFilePath + "activity_giftcard_redeem");
        giftCardClassList.add(actPath + "giftcard.GiftCardSendActivity");
        giftCardClassList.add(resourceFilePath + "activity_giftcard_send");

        classParams.put("GIFTCARD_MODULE", "No");
        for (String item : giftCardClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("GIFTCARD_MODULE", "Yes");
                break;
            }
        }


        ArrayList<String> multiDeliveryClassList = new ArrayList<>();
        multiDeliveryClassList.add(actPath + "ViewMultiDeliveryDetailsActivity");
        multiDeliveryClassList.add(resourceFilePath + "activity_multi_delivery_details");
        multiDeliveryClassList.add("com.model.Delivery_Data");
        multiDeliveryClassList.add("com.model.Trip_Status");
        multiDeliveryClassList.add("com.general.files.MyScrollView");
        multiDeliveryClassList.add("com.adapter.files.MultiDeliveryDetailAdapter");
        multiDeliveryClassList.add(resourceFilePath + "multi_delivery_details_design");
        multiDeliveryClassList.add("com.adapter.files.ViewMultiDeliveryDetailRecyclerAdapter");
        multiDeliveryClassList.add(resourceFilePath + "design_view_multi_delivery_detail");

        classParams.put("MULTI_DELIVERY", "No");
        for (String item : multiDeliveryClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("MULTI_DELIVERY", "Yes");
                break;
            }
        }

        ArrayList<String> uberXClassList = new ArrayList<>();

        uberXClassList.add(actPath + "AddServiceActivity");
        uberXClassList.add(resourceFilePath + "activity_add_service");
        uberXClassList.add(actPath + "MoreServiceInfoActivity");
        uberXClassList.add(resourceFilePath + "activity_more_service_info");
        uberXClassList.add(actPath + "MyGalleryActivity");
        uberXClassList.add(resourceFilePath + "activity_my_gallery");
        uberXClassList.add(actPath + "SetAvailabilityActivity");
        uberXClassList.add(resourceFilePath + "activity_set_availability");
        uberXClassList.add(actPath + "WorkLocationActivity");
        uberXClassList.add(resourceFilePath + "activity_work_location");
        uberXClassList.add(actPath + "UfxCategoryActivity");
        uberXClassList.add(resourceFilePath + "activity_ufx_category");
        uberXClassList.add("com.adapter.files.DaySlotAdapter");
        uberXClassList.add(resourceFilePath + "item_dayslot_view");
        uberXClassList.add("com.adapter.files.PinnedCategorySectionListAdapter");
        uberXClassList.add(resourceFilePath + "category_section_list_item");
        uberXClassList.add("com.adapter.files.GalleryImagesRecyclerAdapter");
        uberXClassList.add(resourceFilePath + "item_gallery_list");
        uberXClassList.add("com.adapter.files.OnGoingTripDetailAdapter");
        uberXClassList.add(resourceFilePath + "item_design_ongoing_trip_cell");
        uberXClassList.add("com.adapter.files.TimeSlotAdapter");
        uberXClassList.add(resourceFilePath + "item_timeslot_view");

        classParams.put("UBERX_SERVICE", "No");
        for (String item : uberXClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                Logger.e("EXIST_FILE", "::" + item);
                classParams.put("UBERX_SERVICE", "Yes");
                break;
            }
        }

        ArrayList<String> newsClassList = new ArrayList<>();
        newsClassList.add(actPath + "NotificationActivity");
        newsClassList.add(resourceFilePath + "activity_notification");
        newsClassList.add(actPath + "NotificationDetailsActivity");
        newsClassList.add(resourceFilePath + "activity_notification_details");
        newsClassList.add("com.fragments.NotiFicationFragment");
        newsClassList.add(resourceFilePath + "fragment_notification");
        newsClassList.add("com.adapter.files.NotificationAdapter");
        newsClassList.add(resourceFilePath + "item_notification_view");

        classParams.put("NEWS_SECTION", "No");
        for (String item : newsClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("NEWS_SECTION", "Yes");
                break;
            }
        }

        ArrayList<String> rentalClassList = new ArrayList<>();
        rentalClassList.add(actPath + "RentalDetailsActivity");
        rentalClassList.add(resourceFilePath + "activity_rental_details");
        rentalClassList.add(actPath + "RentalInfoActivity");
        rentalClassList.add(resourceFilePath + "activity_rental_info");
        rentalClassList.add("com.adapter.files.PackageAdapter");
        rentalClassList.add(resourceFilePath + "item_package_row");

        classParams.put("RENTAL_FEATURE", "No");
        for (String item : rentalClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("RENTAL_FEATURE", "Yes");
                break;
            }
        }


        ArrayList<String> deliveryModuleClassList = new ArrayList<>();
        deliveryModuleClassList.add(actPath + "ViewDeliveryDetailsActivity");
        deliveryModuleClassList.add(resourceFilePath + "activity_view_delivery_details");

        classParams.put("DELIVERY_MODULE", "No");
        for (String item : deliveryModuleClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("DELIVERY_MODULE", "Yes");
                break;
            }
        }


        ArrayList<String> rideModuleClassList = new ArrayList<>();
        rideModuleClassList.add(actPath + "HailActivity");
        rideModuleClassList.add(resourceFilePath + "activity_hail");
        rideModuleClassList.add("com.fragments.CabSelectionFragment");
        rideModuleClassList.add(resourceFilePath + "fragment_new_cab_selection");
        rideModuleClassList.add("com.adapter.files.CabTypeAdapter");
        rideModuleClassList.add(resourceFilePath + "item_design_cab_type");

        classParams.put("RIDE_SECTION", "No");
        for (String item : rideModuleClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("RIDE_SECTION", "Yes");
                break;
            }
        }

        ArrayList<String> rduModuleClassList = new ArrayList<>();
        rduModuleClassList.add(actPath + "AdditionalChargeActivity");
        rduModuleClassList.add(resourceFilePath + "activity_additional_charge");
        rduModuleClassList.add(actPath + "AddAddressActivity");
        rduModuleClassList.add(resourceFilePath + "activity_add_address");
        rduModuleClassList.add(actPath + "WorkingtrekActivity");
        rduModuleClassList.add(resourceFilePath + "activity_active_trip");
        rduModuleClassList.add(actPath + "AddAddressActivity");
        rduModuleClassList.add(resourceFilePath + "activity_add_address");
        rduModuleClassList.add(actPath + "ChatActivity");
        rduModuleClassList.add(resourceFilePath + "design_trip_chat_detail_dialog");
        rduModuleClassList.add(actPath + "CollectPaymentActivity");
        rduModuleClassList.add(resourceFilePath + "activity_collect_payment");
        rduModuleClassList.add(actPath + "ConfirmEmergencyTapActivity");
        rduModuleClassList.add(resourceFilePath + "activity_confirm_emergency_tap");
        rduModuleClassList.add(actPath + "DriverArrivedActivity");
        rduModuleClassList.add(resourceFilePath + "activity_driver_arrived");
        rduModuleClassList.add(actPath + "EmergencyContactActivity");
        rduModuleClassList.add(resourceFilePath + "activity_emergency_contact");
        rduModuleClassList.add(actPath + "FareBreakDownActivity");
        rduModuleClassList.add(resourceFilePath + "activity_fare_break_down");
        rduModuleClassList.add(actPath + "MyBookingsActivity");
        rduModuleClassList.add(resourceFilePath + "activity_my_bookings");

        rduModuleClassList.add(actPath + "PrefranceActivity");
        rduModuleClassList.add(resourceFilePath + "activity_prefrance");
        rduModuleClassList.add(actPath + "RideHistoryActivity");
        rduModuleClassList.add(resourceFilePath + "activity_ride_history");
        rduModuleClassList.add(actPath + "RideHistoryDetailActivity");
        rduModuleClassList.add(resourceFilePath + "activity_ride_history_detail");
        rduModuleClassList.add(actPath + "SearchPickupLocationActivity");
        rduModuleClassList.add(resourceFilePath + "activity_search_pickup_location");
        rduModuleClassList.add(actPath + "SearchPickupLocationActivity");
        rduModuleClassList.add(resourceFilePath + "activity_search_pickup_location");
        rduModuleClassList.add(actPath + "SearchLocationActivity");
        rduModuleClassList.add(resourceFilePath + "activity_search_location");
        rduModuleClassList.add(actPath + "SelectedDayHistoryActivity");
        rduModuleClassList.add(resourceFilePath + "activity_selected_day_history");
        rduModuleClassList.add("com.fragments.BookingFragment");
        rduModuleClassList.add(resourceFilePath + "fragment_booking");
        rduModuleClassList.add("com.fragments.DeliveryFragment");
        rduModuleClassList.add(resourceFilePath + "fragment_delivery");
        rduModuleClassList.add("com.fragments.RideHistoryFragment");
        rduModuleClassList.add(resourceFilePath + "activity_ride_history");
        rduModuleClassList.add("com.fragments.MainHeaderFragment");
        rduModuleClassList.add(resourceFilePath + "fragment_main_header");
        rduModuleClassList.add("com.adapter.files.ChatMessage");
        rduModuleClassList.add("com.adapter.files.ChatMessagesRecycleAdapter");
        rduModuleClassList.add(resourceFilePath + "message");
        rduModuleClassList.add("com.adapter.files.CustSpinnerAdapter");
        rduModuleClassList.add(resourceFilePath + "item_spinnertextview");
        rduModuleClassList.add("com.adapter.files.EmergencyContactRecycleAdapter");
        rduModuleClassList.add(resourceFilePath + "emergency_contact_item");
        rduModuleClassList.add("com.general.files.OpenPassengerDetailDialog");
        rduModuleClassList.add(resourceFilePath + "design_passenger_detail_dialog");
        rduModuleClassList.add("com.general.files.CancelTripDialog");
        rduModuleClassList.add(resourceFilePath + "decline_order_dialog_design");
        rduModuleClassList.add("com.adapter.files.CategoryListItem");

        classParams.put("RDU_SECTION", "No");
        for (String item : rduModuleClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("RDU_SECTION", "Yes");
                break;
            }
        }

        ArrayList<String> tollModuleClassList = new ArrayList<>();
        tollModuleClassList.add("com.utils.CommonUtilities.TOLLURL");

        classParams.put("TOLL_MODULE", "No");

        Class<?> commonUtilsClz = CommonUtilities.class;
        try {
            Field field_chk = commonUtilsClz.getField("TOLLURL");
            if (field_chk != null) {
                classParams.put("TOLL_MODULE", "Yes");
            } else {
                classParams.put("TOLL_MODULE", "No");
            }
        } catch (Exception ex) {
            classParams.put("TOLL_MODULE", "No");
        }

        ArrayList<String> endOfDayModuleClassList = new ArrayList<>();
        //endOfDayModuleClassList.add(actPath + "FavouriteDriverActivity");
        endOfDayModuleClassList.add("com.adapter.files.RecentLocationAdpater");
        endOfDayModuleClassList.add(resourceFilePath + "design_end_day_start_trip");
        endOfDayModuleClassList.add(resourceFilePath + "item_recent_loc_design");

        classParams.put("END_OF_DAY_TRIP_SECTION", "No");
        for (String item : endOfDayModuleClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("END_OF_DAY_TRIP_SECTION", "Yes");
                break;
            }
        }

        ArrayList<String> stopOverPointModuleClassList = new ArrayList<>();
        stopOverPointModuleClassList.add("com.adapter.files.ViewStopOverDetailRecyclerAdapter");
        stopOverPointModuleClassList.add(actPath + "ViewStopOverDetailsActivity");
        stopOverPointModuleClassList.add(resourceFilePath + "activity_stop_over_details");
        stopOverPointModuleClassList.add(resourceFilePath + "design_view_stop_over_detail");

        classParams.put("STOP_OVER_POINT_SECTION", "No");
        for (String item : stopOverPointModuleClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("STOP_OVER_POINT_SECTION", "Yes");
                break;
            }
        }

        ArrayList<String> driverSubscriptionModuleClassList = new ArrayList<>();
        driverSubscriptionModuleClassList.add("com.adapter.files.SubscriptionAdapter");
        driverSubscriptionModuleClassList.add(resourceFilePath + "item_subscription_history");
        driverSubscriptionModuleClassList.add(resourceFilePath + "item_subscription");
        driverSubscriptionModuleClassList.add(actPath + "SubscriptionActivity");
        driverSubscriptionModuleClassList.add(resourceFilePath + "activity_subscription");
        driverSubscriptionModuleClassList.add(actPath + "SubscriptionHistoryActivity");
        driverSubscriptionModuleClassList.add(resourceFilePath + "activity_subscription");
        driverSubscriptionModuleClassList.add(actPath + "SubscriptionPaymentActivity");
        driverSubscriptionModuleClassList.add(resourceFilePath + "activity_subscription_payment");

        classParams.put("DRIVER_SUBSCRIPTION_SECTION", "No");
        for (String item : driverSubscriptionModuleClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("DRIVER_SUBSCRIPTION_SECTION", "Yes");
                break;
            }
        }


        ArrayList<String> goPayModuleClassList = new ArrayList<>();
        goPayModuleClassList.add(resourceFilePath + "design_transfer_money");

        classParams.put("GO_PAY_SECTION", "No");
        for (String item : goPayModuleClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("GO_PAY_SECTION", "Yes");
                break;
            }
        }

        ArrayList<String> flyClassList = new ArrayList<>();
        flyClassList.add("com.adapter.files.SkyPortsRecyclerAdapter");
        flyClassList.add(resourceFilePath + "design_choose_skyports");
        flyClassList.add("com.fragments.FlyStationSelectionFragment");
        flyClassList.add(resourceFilePath + "design_skyports_bottom_view");
        classParams.put("FLY_MODULE", "No");
        for (String item : flyClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("FLY_MODULE", "Yes");
                break;
            }
        }


        /** Removal file of libraries **/
        voipServiceClassList.add("libs/sinch_lib.aar");
        voipServiceClassList.add("Libs folder remove file called 'sinch_lib' Or any lib which is related to SINCH");
        cardIOClassList.add("Go to App's Level build.Gradle File and Remove Library 'io.card:android-sdk'");
        liveChatClassList.add("Go to App's Level build.Gradle File and Remove Library 'com.github.livechat:chat-window-android'");
        tollModuleClassList.add("Remove Declaration of Toll URL from CommonUtilities File And remove portion of Toll cost from code. (Remove Network execution of toll URL)");
        /** Removal file of libraries **/

        if (classParams.get("SAFETY_TOOLS_MODULE") != null && classParams.get("SAFETY_TOOLS_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("SAFETY_TOOLS_MODULE_FILES", android.text.TextUtils.join(",", safetyToolsClassList));
        }

        if (classParams.get("BID_SERVICE") != null && classParams.get("BID_SERVICE").equalsIgnoreCase("Yes")) {
            classParams.put("BID_SERVICE_FILES", android.text.TextUtils.join(",", biddingClassList));
        }

        if (classParams.get("WAYBILL_MODULE") != null && classParams.get("WAYBILL_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("WAYBILL_MODULE_FILES", android.text.TextUtils.join(",", wayBillClassList));
        }

        if (classParams.get("VOIP_SERVICE") != null && classParams.get("VOIP_SERVICE").equalsIgnoreCase("Yes")) {
            classParams.put("VOIP_SERVICE_FILES", android.text.TextUtils.join(",", voipServiceClassList));
        }

        if (classParams.get("ADVERTISEMENT_MODULE") != null && classParams.get("ADVERTISEMENT_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("ADVERTISEMENT_MODULE_FILES", android.text.TextUtils.join(",", advertisementClassList));
        }

        if (classParams.get("LINKEDIN_MODULE") != null && classParams.get("LINKEDIN_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("LINKEDIN_MODULE_FILES", android.text.TextUtils.join(",", linkedInClassList));
        }

        if (classParams.get("CARD_IO") != null && classParams.get("CARD_IO").equalsIgnoreCase("Yes")) {
            classParams.put("CARD_IO_FILES", android.text.TextUtils.join(",", cardIOClassList));
        }

        if (classParams.get("LIVE_CHAT") != null && classParams.get("LIVE_CHAT").equalsIgnoreCase("Yes")) {
            classParams.put("LIVE_CHAT_FILES", android.text.TextUtils.join(",", liveChatClassList));
        }

        if (classParams.get("DELIVER_ALL") != null && classParams.get("DELIVER_ALL").equalsIgnoreCase("Yes")) {
            classParams.put("DELIVER_ALL_FILES", android.text.TextUtils.join(",", deliverAllClassList));
        }

        if (classParams.get("MULTI_DELIVERY") != null && classParams.get("MULTI_DELIVERY").equalsIgnoreCase("Yes")) {
            classParams.put("MULTI_DELIVERY_FILES", android.text.TextUtils.join(",", multiDeliveryClassList));
        }

        if (classParams.get("GIFTCARD_MODULE") != null && classParams.get("GIFTCARD_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("GIFTCARD_MODULE_FILES", android.text.TextUtils.join(",", giftCardClassList));
        }

        if (classParams.get("UBERX_SERVICE") != null && classParams.get("UBERX_SERVICE").equalsIgnoreCase("Yes")) {
            classParams.put("UBERX_FILES", android.text.TextUtils.join(",", uberXClassList));
        }

        if (classParams.get("NEWS_SECTION") != null && classParams.get("NEWS_SECTION").equalsIgnoreCase("Yes")) {
            classParams.put("NEWS_SERVICE_FILES", android.text.TextUtils.join(",", newsClassList));
        }

        if (classParams.get("RENTAL_FEATURE") != null && classParams.get("RENTAL_FEATURE").equalsIgnoreCase("Yes")) {
            classParams.put("RENTAL_SERVICE_FILES", android.text.TextUtils.join(",", rentalClassList));
        }

        if (classParams.get("DELIVERY_MODULE") != null && classParams.get("DELIVERY_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("DELIVERY_MODULE_FILES", android.text.TextUtils.join(",", deliveryModuleClassList));
        }

        if (classParams.get("RIDE_SECTION") != null && classParams.get("RIDE_SECTION").equalsIgnoreCase("Yes")) {
            classParams.put("RIDE_SECTION_FILES", android.text.TextUtils.join(",", rideModuleClassList));
        }

        if (classParams.get("RDU_SECTION") != null && classParams.get("RDU_SECTION").equalsIgnoreCase("Yes")) {
            classParams.put("RDU_SECTION_FILES", android.text.TextUtils.join(",", rduModuleClassList));
        }

        if (classParams.get("TOLL_MODULE") != null && classParams.get("TOLL_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("TOLL_MODULE_FILES", android.text.TextUtils.join(",", tollModuleClassList));
        }

        if (classParams.get("STOP_OVER_POINT_SECTION") != null && classParams.get("STOP_OVER_POINT_SECTION").equalsIgnoreCase("Yes")) {
            classParams.put("STOP_OVER_POINT_SECTION_FILES", android.text.TextUtils.join(",", stopOverPointModuleClassList));
        }

        if (classParams.get("END_OF_DAY_TRIP_SECTION") != null && classParams.get("END_OF_DAY_TRIP_SECTION").equalsIgnoreCase("Yes")) {
            classParams.put("END_OF_DAY_TRIP_SECTION_FILES", android.text.TextUtils.join(",", endOfDayModuleClassList));
        }

        if (classParams.get("DRIVER_SUBSCRIPTION_SECTION") != null && classParams.get("DRIVER_SUBSCRIPTION_SECTION").equalsIgnoreCase("Yes")) {
            classParams.put("DRIVER_SUBSCRIPTION_SECTION_FILES", android.text.TextUtils.join(",", driverSubscriptionModuleClassList));
        }

        if (classParams.get("GO_PAY_SECTION") != null && classParams.get("GO_PAY_SECTION").equalsIgnoreCase("Yes")) {
            classParams.put("GO_PAY_SECTION_FILES", android.text.TextUtils.join(",", goPayModuleClassList));
        }

        if (classParams.get("FLY_MODULE") != null && classParams.get("FLY_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("FLY_MODULE_FILES", android.text.TextUtils.join(",", flyClassList));
        }

        classParams.put("PACKAGE_NAME", BuildConfig.APPLICATION_ID);

        return classParams;
    }
}