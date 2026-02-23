package com.general.files;

import com.buddyverse.main.BuildConfig;
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
    private static final String drawableFileFilePath = "res/drawable/";

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
        biddingClassList.add(actPath + "RequestBidInfoActivity");
        biddingClassList.add(resourceFilePath + "activity_request_bid_info");
        biddingClassList.add("com.fragments.BiddingBookingFragment");
        biddingClassList.add(resourceFilePath + "fragment_bidding");
        biddingClassList.add(actPath + "BiddingTaskActivity");
        biddingClassList.add(resourceFilePath + "activity_bidding_task");
        biddingClassList.add("com.adapter.files.BiddingReceivedRecycleAdapter");
        biddingClassList.add(resourceFilePath + "item_bid_received_design");
        biddingClassList.add(actPath + "BiddingHistoryDetailActivity");
        biddingClassList.add(resourceFilePath + "activity_bidding_history_detail");
        biddingClassList.add("com.adapter.files.AllBiddingRecycleAdapter");
        biddingClassList.add(resourceFilePath + "item_bidding_layout");
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

        //InterCity

        ArrayList<String> interCityClassList = new ArrayList<>();
        interCityClassList.add(actPath + "intercity.IntercityHomeActivity");
        interCityClassList.add(resourceFilePath + "activity_intercity_home");
        interCityClassList.add(actPath + "intercity.IntercityDateTimeSelectorActivity");
        interCityClassList.add(resourceFilePath + "activity_intercity_date_time_selector");

        interCityClassList.add(actPath + "intercity.fragment.OneWayTripFragment");
        interCityClassList.add(resourceFilePath + "fragment_one_way_trip");
        interCityClassList.add(actPath + "intercity.fragment.RoundTripFragment");
        interCityClassList.add(resourceFilePath + "fragment_round_trip");

        interCityClassList.add(actPath + "intercity.Models.TripConfigModel");


        classParams.put("INTERCITY_MODULE", "No");
        for (String item : interCityClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("INTERCITY_MODULE", "Yes");
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

        //Live Activity Module
        ArrayList<String> liveActivityClassList = new ArrayList<>();
        liveActivityClassList.add("com.general.files.LiveActivityNotification");
        liveActivityClassList.add(resourceFilePath + "notification_live_activity_view");

        classParams.put("LIVE_ACTIVITY_MODULE", "No");
        for (String item : liveActivityClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("LIVE_ACTIVITY_MODULE", "Yes");
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

        //nearbyservice Module

        ArrayList<String> nearByServiceClassList = new ArrayList<>();
        nearByServiceClassList.add(actPath + "nearbyservice.adapter.NearByCategoryAdapter");
        nearByServiceClassList.add(resourceFilePath + "item_near_by_category");
        nearByServiceClassList.add(actPath + "nearbyservice.adapter.NearByServiceAdapter");
        nearByServiceClassList.add(resourceFilePath + "item_near_by_services");
        nearByServiceClassList.add(actPath + "nearbyservice.adapter.ServiceActionAdapter");
        nearByServiceClassList.add(resourceFilePath + "item_near_by_service_action");
        nearByServiceClassList.add(actPath + "nearbyservice.NearByDetailsActivity");
        nearByServiceClassList.add(resourceFilePath + "activity_near_by_details");
        nearByServiceClassList.add(actPath + "nearbyservice.NearByServicesActivity");
        nearByServiceClassList.add(resourceFilePath + "activity_near_by_service");

        classParams.put("NEARBY_MODULE", "No");
        for (String item : nearByServiceClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("NEARBY_MODULE", "Yes");
                break;
            }
        }

        //RentItem Module
        ArrayList<String> rentItemClassList = new ArrayList<>();
        rentItemClassList.add(actPath + "rentItem.adapter.PhotosAdapter");
        rentItemClassList.add(actPath + "rentItem.adapter.RentCategoryAdapter");
        rentItemClassList.add(resourceFilePath + "item_rent_item_category");
        rentItemClassList.add(actPath + "rentItem.adapter.RentCategorySubCategoryAdapter");
        rentItemClassList.add(resourceFilePath + "item_rent_item_sub_category");
        rentItemClassList.add(actPath + "rentItem.adapter.RentGalleryImagesAdapter");
        rentItemClassList.add(resourceFilePath + "item_rent_gallery_list");
        rentItemClassList.add(actPath + "rentItem.adapter.RentItemDataRecommendedAdapter");
        rentItemClassList.add(resourceFilePath + "item_rent_item_post");
        rentItemClassList.add(actPath + "rentItem.adapter.RentItemListPostAdapter");
        rentItemClassList.add(resourceFilePath + "item_rent_item_list_post");
        rentItemClassList.add(actPath + "rentItem.adapter.RentItemStepsAdapter");
        rentItemClassList.add(resourceFilePath + "item_rent_step_view");
        rentItemClassList.add(actPath + "rentItem.adapter.RentPaymentPlanAdapter");
        rentItemClassList.add(resourceFilePath + "item_rent_item_payment_plan");
        rentItemClassList.add(actPath + "rentItem.adapter.RentPickupTimeSlotAdapter");
        rentItemClassList.add(resourceFilePath + "item_rent_pickup_time_slot");

        rentItemClassList.add(actPath + "rentItem.fragment.RentCategoryFragment");
        rentItemClassList.add(resourceFilePath + "fragment_rent_category");
        rentItemClassList.add(actPath + "rentItem.fragment.RentItemDynamicDetailsFragment");
        rentItemClassList.add(resourceFilePath + "fragment_rent_item_dynamic_details");
        rentItemClassList.add(actPath + "rentItem.fragment.RentItemLocationDetailsFragment");
        rentItemClassList.add(resourceFilePath + "fragment_rent_item_location_details");
        rentItemClassList.add(actPath + "rentItem.fragment.RentItemPaymentPlanFragment");
        rentItemClassList.add(resourceFilePath + "fragment_rent_item_payment_plan_details");
        rentItemClassList.add(actPath + "rentItem.fragment.RentItemPhotosFragment");
        rentItemClassList.add(resourceFilePath + "fragment_rent_item_photos");
        rentItemClassList.add(actPath + "rentItem.fragment.RentItemPickupAvailabilityFragment");
        rentItemClassList.add(resourceFilePath + "fragment_rent_item_pickup_availability");
        rentItemClassList.add(actPath + "rentItem.fragment.RentItemPricingDetailsFragment");
        rentItemClassList.add(resourceFilePath + "fragment_rent_item_pricing_details");
        rentItemClassList.add(actPath + "rentItem.fragment.RentItemReviewAllDetailsFragment");
        rentItemClassList.add(resourceFilePath + "fragment_rent_item_review_all_details");
        rentItemClassList.add(actPath + "rentItem.fragment.RentSubCategoryFragment");
        rentItemClassList.add(resourceFilePath + "fragment_rent_sub_category");
        rentItemClassList.add(actPath + "rentItem.model.RentItemData");

        rentItemClassList.add(actPath + "rentItem.RentItemFilterActivity");
        rentItemClassList.add(resourceFilePath + "activity_rent_item_filter");
        rentItemClassList.add(resourceFilePath + "item_rent_item_filter_header");
        rentItemClassList.add(resourceFilePath + "item_rent_item_filter_service");
        rentItemClassList.add(actPath + "rentItem.RentItemHomeActivity");
        rentItemClassList.add(resourceFilePath + "activity_rent_item_home");
        rentItemClassList.add(actPath + "rentItem.RentItemInquiry");
        rentItemClassList.add(resourceFilePath + "activity_rent_item_inquiry");
        rentItemClassList.add(actPath + "rentItem.RentItemListPostActivity");
        rentItemClassList.add(resourceFilePath + "activity_rent_item_list_post");
        rentItemClassList.add(actPath + "rentItem.RentItemNewPostActivity");
        rentItemClassList.add(resourceFilePath + "activity_rent_item_new_post");
        rentItemClassList.add(actPath + "rentItem.RentItemOwnerInfo");
        rentItemClassList.add(resourceFilePath + "activity_rent_item_owner_info");
        rentItemClassList.add(actPath + "rentItem.RentItemReviewPostActivity");
        rentItemClassList.add(resourceFilePath + "activity_rent_item_review_post");

        // Only BSR files
        rentItemClassList.add(actPath + "rentItem.fragment.RentItemListFragment");
        rentItemClassList.add(resourceFilePath + "activity_uber_xhome_23_buy_sell_rent_only");
        rentItemClassList.add(resourceFilePath + "item_23_list_buy_sell_rent_only");

        classParams.put("RENT_ITEM_SERVICE", "No");
        for (String item : rentItemClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("RENT_ITEM_SERVICE", "Yes");
                break;
            }
        }

        //Parking Module
        ArrayList<String> parkingClassList = new ArrayList<>();
        parkingClassList.add(actPath + "parking.adapter.MyParkingSpaceAdapter");
        parkingClassList.add(resourceFilePath + "item_my_parking_space_list");
        parkingClassList.add(actPath + "parking.adapter.ParkingDatesRecyclerAdapter");
        parkingClassList.add(resourceFilePath + "item_dates_design_parking");
        parkingClassList.add(actPath + "parking.adapter.ParkingDocumentAdapter");
        parkingClassList.add(resourceFilePath + "item_parking_document");
        parkingClassList.add(actPath + "parking.adapter.ParkingImagesAdapterNew");
        parkingClassList.add(resourceFilePath + "item_parking_media");
        parkingClassList.add(actPath + "parking.adapter.ParkingListAdapter");
        parkingClassList.add(resourceFilePath + "item_parking_list");
        parkingClassList.add(actPath + "parking.adapter.ParkingReviewsAdapter");
        parkingClassList.add(resourceFilePath + "item_parking_review");
        parkingClassList.add(actPath + "parking.adapter.ParkingTimeSlotAdapter");
        parkingClassList.add(resourceFilePath + "item_arrival_timeslot_view_parking");
        parkingClassList.add(resourceFilePath + "item_timeslot_view_parking");
        parkingClassList.add(actPath + "parking.adapter.ParkingVehicleSizeAdapter");
        parkingClassList.add(resourceFilePath + "item_parking_vehicle_size");
        parkingClassList.add(actPath + "parking.adapter.VehicleListAdapter");
        parkingClassList.add(resourceFilePath + "item_vehicle_list_parking");
        parkingClassList.add(actPath + "parking.adapter.VehicleSizeAdapter");
        parkingClassList.add(resourceFilePath + "item_vehicle_size");

        parkingClassList.add(actPath + "parking.fragment.ParkingBookingFragment");
        parkingClassList.add(resourceFilePath + "fragment_parking_booking");
        parkingClassList.add(actPath + "parking.fragment.ParkingInformationFragment");
        parkingClassList.add(resourceFilePath + "parking_information_fragment_layout");
        parkingClassList.add(actPath + "parking.fragment.ParkingListFragment");
        parkingClassList.add(resourceFilePath + "parking_list_fragment_layout");
        parkingClassList.add(actPath + "parking.fragment.ParkingListMapFragment");
        parkingClassList.add(resourceFilePath + "parking_list_map_fragment_layout");
        parkingClassList.add(actPath + "parking.fragment.ParkingLocationFragment");
        parkingClassList.add(resourceFilePath + "parking_location_fragment_layout");
        parkingClassList.add(actPath + "parking.fragment.ParkingPublishFragment");
        parkingClassList.add(resourceFilePath + "fragment_parking_publish");

        parkingClassList.add(actPath + "parking.fragment.ParkingPublishStep1Fragment");
        parkingClassList.add(resourceFilePath + "fragment_parking_publish_step_1");
        parkingClassList.add(actPath + "parking.fragment.ParkingPublishStep2Fragment");
        parkingClassList.add(resourceFilePath + "fragment_parking_publish_step_2");
        parkingClassList.add(actPath + "parking.fragment.ParkingPublishStep3Fragment");
        parkingClassList.add(resourceFilePath + "fragment_parking_publish_step_3");
        parkingClassList.add(actPath + "parking.fragment.ParkingPublishStep4Fragment");
        parkingClassList.add(resourceFilePath + "fragment_parking_publish_step_4");
        parkingClassList.add(actPath + "parking.fragment.ParkingReviewsFragment");
        parkingClassList.add(resourceFilePath + "parking_reviews_fragment_layout");

        parkingClassList.add(actPath + "parking.model.BookParkingData");
        parkingClassList.add(actPath + "parking.model.ParkingPublishData");
        parkingClassList.add(resourceFilePath + "item_book_parking_marker");

        parkingClassList.add(actPath + "parking.AddParkingCarOrDriverActivity");
        parkingClassList.add(resourceFilePath + "activity_parking_add_car_driver");
        parkingClassList.add(actPath + "parking.AvailableParkingSpacesActivity");
        parkingClassList.add(resourceFilePath + "activity_available_parking_spaces");
        parkingClassList.add(actPath + "parking.BookParking");
        parkingClassList.add(resourceFilePath + "activity_book_parking");
        parkingClassList.add(actPath + "parking.ParkingArrivalScheduleActivity");
        parkingClassList.add(resourceFilePath + "activity_parking_arrival_schedule");
        parkingClassList.add(actPath + "parking.ParkingDetailsActivity");
        parkingClassList.add(resourceFilePath + "activity_parking_details");
        parkingClassList.add(actPath + "parking.ParkingPublish");
        parkingClassList.add(resourceFilePath + "activity_publish_parking");
        parkingClassList.add(actPath + "parking.ParkingPublishAndBooking");
        parkingClassList.add(resourceFilePath + "activity_parking_my_list");
        parkingClassList.add(actPath + "parking.ParkingUploadDocActivity");
        parkingClassList.add(resourceFilePath + "activity_parking_upload_doc");
        parkingClassList.add(actPath + "parking.ParkingVehicleListActivity");
        parkingClassList.add(resourceFilePath + "activity_parking_vehicle_list");
        parkingClassList.add(actPath + "parking.ReviewOrCancelParkingBookingActivity");
        parkingClassList.add(resourceFilePath + "activity_review_parking_booking");

        classParams.put("PARKING_MODULE", "No");
        for (String item : parkingClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("PARKING_MODULE", "Yes");
                break;
            }
        }

        //Pro RideSharing Module
        ArrayList<String> proRideSharingClassList = new ArrayList<>();
        proRideSharingClassList.add(actPath + "rideSharingPro.adapter.EditPriceSerSeatAdapter");
        proRideSharingClassList.add(resourceFilePath + "item_ride_pro_edit_price");
        proRideSharingClassList.add(actPath + "rideSharingPro.adapter.ListOfDocAdapter");
        proRideSharingClassList.add(resourceFilePath + "list_of_doc_item_design");
        proRideSharingClassList.add(actPath + "rideSharingPro.adapter.MultiStopAdapter");
        proRideSharingClassList.add(resourceFilePath + "item_ride_pro_multi_stop");
        proRideSharingClassList.add(actPath + "rideSharingPro.adapter.RecentPostRideAdapter");
        proRideSharingClassList.add(resourceFilePath + "item_ride_pro_recent_post_rides");
        proRideSharingClassList.add(actPath + "rideSharingPro.adapter.RideBookSearchAdapter");
        proRideSharingClassList.add(resourceFilePath + "item_ride_book_search");
        proRideSharingClassList.add(actPath + "rideSharingPro.adapter.RideDocumentAdapter");
        proRideSharingClassList.add(resourceFilePath + "item_ride_sharing_document");
        proRideSharingClassList.add(actPath + "rideSharingPro.adapter.RideMyPassengerAdapter");
        proRideSharingClassList.add(resourceFilePath + "item_ride_my_passenger_list");
        proRideSharingClassList.add(actPath + "rideSharingPro.adapter.RideMyPublishAdapter");
        proRideSharingClassList.add(resourceFilePath + "item_ride_my_list");
        proRideSharingClassList.add(actPath + "rideSharingPro.adapter.RideShareRiderDetailsAdapter");
        proRideSharingClassList.add(resourceFilePath + "carpool_riders_detail");

        proRideSharingClassList.add(actPath + "rideSharingPro.fragment.RideBookingFragment");
        proRideSharingClassList.add(resourceFilePath + "fragment_ride_booking");
        proRideSharingClassList.add(actPath + "rideSharingPro.fragment.RidePublishFragment");
        proRideSharingClassList.add(resourceFilePath + "fragment_ride_publish");
        proRideSharingClassList.add(actPath + "rideSharingPro.fragment.RidePublishStep1Fragment");
        proRideSharingClassList.add(resourceFilePath + "fragment_ride_publish_step_1");
        proRideSharingClassList.add(actPath + "rideSharingPro.fragment.RidePublishStep1MultiStopFragment");
        proRideSharingClassList.add(resourceFilePath + "fragment_ride_publish_step_1_muti_stop");
        proRideSharingClassList.add(actPath + "rideSharingPro.fragment.RidePublishStep2Fragment");
        proRideSharingClassList.add(resourceFilePath + "fragment_ride_publish_step_2");
        proRideSharingClassList.add(actPath + "rideSharingPro.fragment.RidePublishStep3Fragment");
        proRideSharingClassList.add(resourceFilePath + "fragment_ride_publish_step_3");
        proRideSharingClassList.add(actPath + "rideSharingPro.fragment.RidePublishStep4Fragment");
        proRideSharingClassList.add(resourceFilePath + "fragment_ride_publish_step_4");

        proRideSharingClassList.add(actPath + "rideSharingPro.fragmentHome.RideSharingPublishFragment");
        proRideSharingClassList.add(resourceFilePath + "activity_ride_publish");
        proRideSharingClassList.add(actPath + "rideSharingPro.fragmentHome.RideSharingRidesFragment");
        proRideSharingClassList.add(resourceFilePath + "activity_ride_my_list");
        proRideSharingClassList.add(actPath + "rideSharingPro.fragmentHome.RideSharingSearchFragment");
        proRideSharingClassList.add(resourceFilePath + "fragment_ride_sharing_search");
        proRideSharingClassList.add(actPath + "rideSharingPro.model.RideProPublishData");

        proRideSharingClassList.add(actPath + "rideSharingPro.ListOfDocumentActivity");
        proRideSharingClassList.add(resourceFilePath + "activity_list_of_document");
        proRideSharingClassList.add(actPath + "rideSharingPro.RideBookDetails");
        proRideSharingClassList.add(resourceFilePath + "activity_ride_book_details");
        proRideSharingClassList.add(actPath + "rideSharingPro.RideBookingRequestedActivity");
        proRideSharingClassList.add(resourceFilePath + "activity_ride_booking_request");

        proRideSharingClassList.add(actPath + "rideSharingPro.RideBookSearchList");
        proRideSharingClassList.add(resourceFilePath + "activity_ride_book_search");
        proRideSharingClassList.add(actPath + "rideSharingPro.RideBookSummary");
        proRideSharingClassList.add(resourceFilePath + "activity_ride_book_summary");
        proRideSharingClassList.add(actPath + "rideSharingPro.RideMyDetails");
        proRideSharingClassList.add(resourceFilePath + "activity_ride_my_details");
        proRideSharingClassList.add(actPath + "rideSharingPro.RideMyList");

        proRideSharingClassList.add(actPath + "rideSharingPro.RideShareActiveTripActivity");
        proRideSharingClassList.add(resourceFilePath + "activity_ride_share_active_trip");
        proRideSharingClassList.add(actPath + "rideSharingPro.RideSharePaymentSummaryActivity");
        proRideSharingClassList.add(resourceFilePath + "activity_ride_share_payment_summary");

        proRideSharingClassList.add(actPath + "rideSharingPro.RideSharingProHomeActivity");
        proRideSharingClassList.add(resourceFilePath + "activity_ride_sharing_home");
        proRideSharingClassList.add(actPath + "rideSharingPro.RideSharingProPreferences");
        proRideSharingClassList.add(resourceFilePath + "activity_ride_sharing_preferences");
        proRideSharingClassList.add(actPath + "rideSharingPro.RideUploadDocActivity");
        proRideSharingClassList.add(resourceFilePath + "activity_ride_upload_doc");

        proRideSharingClassList.add(actPath + "rideSharingPro.RideSharingUtils");
        proRideSharingClassList.add(resourceFilePath + "seat_selection_dialog_23");
        proRideSharingClassList.add(resourceFilePath + "item_ride_pro_multi_stop_address");
        proRideSharingClassList.add(resourceFilePath + "item_ride_details_summary");
        proRideSharingClassList.add(resourceFilePath + "dialog_ride_rating");

        classParams.put("RIDE_SHARE_PRO_MODULE", "No");
        for (String item : proRideSharingClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("RIDE_SHARE_PRO_MODULE", "Yes");
                break;
            }
        }

        //Tracking Module
        ArrayList<String> trackServiceClassList = new ArrayList<>();
        trackServiceClassList.add(actPath + "trackService.adapter.TrackAnyAdapter");
        trackServiceClassList.add(resourceFilePath + "item_track_any_user");
        trackServiceClassList.add(actPath + "trackService.PairCodeGenrateActivity");
        trackServiceClassList.add(resourceFilePath + "activity_pair_code_genrate");

        trackServiceClassList.add(actPath + "trackService.TrackAnyList");
        trackServiceClassList.add(resourceFilePath + "activity_track_any_list");
        trackServiceClassList.add(actPath + "trackService.TrackAnyLiveTracking");
        trackServiceClassList.add(resourceFilePath + "activity_track_any_live_tracking");
        trackServiceClassList.add(actPath + "trackService.TrackAnyProfileSetup");
        trackServiceClassList.add(resourceFilePath + "activity_track_any_profile_setup");
        trackServiceClassList.add(actPath + "trackService.TrackAnyProfileVehicle");
        trackServiceClassList.add(resourceFilePath + "activity_track_any_profile_vehicle");

        classParams.put("TRACKING_MODULE", "No");
        for (String item : trackServiceClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("TRACKING_MODULE", "Yes");
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

        ArrayList<String> poolClassList = new ArrayList<>();
        poolClassList.add("com.adapter.files.PoolSeatsSelectionAdapter");
        poolClassList.add(resourceFilePath + "design_no_of_seats_pool");

        classParams.put("POOL_MODULE", "No");
        for (String item : poolClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("POOL_MODULE", "Yes");
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

        ArrayList<String> goPayModuleClassList = new ArrayList<>();
        goPayModuleClassList.add(resourceFilePath + "design_transfer_money");

        classParams.put("GO_PAY_SECTION", "No");
        for (String item : goPayModuleClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("GO_PAY_SECTION", "Yes");
                break;
            }
        }

        ArrayList<String> deliverAllGeneralClassList = new ArrayList<>();
        deliverAllGeneralClassList.add(actPath + "PrescriptionActivity");
        deliverAllGeneralClassList.add(resourceFilePath + "activity_prescription");
        deliverAllGeneralClassList.add(actPath + "PrescriptionHistoryImagesActivity");
        deliverAllGeneralClassList.add(resourceFilePath + "activity_prescription_history_images");


        classParams.put("DELIVER_ALL_GENERAL", "No");
        for (String item : deliverAllGeneralClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("DELIVER_ALL_GENERAL", "Yes");
                break;
            }
        }


        ArrayList<String> deliverAllClassList = new ArrayList<>();
        deliverAllClassList.add(actPath + "deliverAll.ActiveOrderActivity");
        deliverAllClassList.add(resourceFilePath + "activity_active_order");
        deliverAllClassList.add(actPath + "deliverAll.AddBasketActivity");
        deliverAllClassList.add(resourceFilePath + "activity_add_basket");
        deliverAllClassList.add(resourceFilePath + "item_basket_option");
        deliverAllClassList.add(resourceFilePath + "item_basket_toping");
        deliverAllClassList.add(actPath + "deliverAll.CheckOutActivity");
        deliverAllClassList.add(resourceFilePath + "activity_check_out");
        deliverAllClassList.add(resourceFilePath + "item_checkout_row");
        deliverAllClassList.add(actPath + "deliverAll.EditCartActivity");
        deliverAllClassList.add(resourceFilePath + "activity_edit_cart");
        deliverAllClassList.add(resourceFilePath + "item_edit_cart_row");
        deliverAllClassList.add(resourceFilePath + "dialog_cart_edit_options");
        deliverAllClassList.add(resourceFilePath + "item_option");
        deliverAllClassList.add(resourceFilePath + "item_toping");
        deliverAllClassList.add(resourceFilePath + "dialog_cart_repeat");
        deliverAllClassList.add(actPath + "deliverAll.FoodDeliveryHomeActivity");
        deliverAllClassList.add(actPath + "deliverAll.FoodDeliveryHomeActivity24");
        deliverAllClassList.add(resourceFilePath + "deliver_all_dialog_filter");
        deliverAllClassList.add(resourceFilePath + "item_filter");
        deliverAllClassList.add(resourceFilePath + "dialog_relevance");
        deliverAllClassList.add(actPath + "deliverAll.FoodRatingActivity");
        deliverAllClassList.add(resourceFilePath + "activity_food_rating");
        deliverAllClassList.add(actPath + "deliverAll.LoginActivity");
        deliverAllClassList.add(resourceFilePath + "activity_login");
        deliverAllClassList.add(actPath + "deliverAll.OrderDetailsActivity");
        deliverAllClassList.add(resourceFilePath + "activity_order_details");
        deliverAllClassList.add(actPath + "deliverAll.OrderPlaceConfirmActivity");
        deliverAllClassList.add(resourceFilePath + "activity_order_place_confirm");
        deliverAllClassList.add(resourceFilePath + "item_cusines");
        deliverAllClassList.add(actPath + "deliverAll.RestaurantAllDetailsNewActivity");
        deliverAllClassList.add(resourceFilePath + "activity_restaurant_all_details_new");
        deliverAllClassList.add(actPath + "deliverAll.RestaurantsSearchActivity");
        deliverAllClassList.add(resourceFilePath + "activity_restaurants_search");
        deliverAllClassList.add(actPath + "deliverAll.SearchFoodActivity");
        deliverAllClassList.add(resourceFilePath + "activity_search_food");
        deliverAllClassList.add(actPath + "deliverAll.SearchRestaurantListActivity");
        deliverAllClassList.add(resourceFilePath + "activity_search_restaurant_list");
        deliverAllClassList.add(actPath + "deliverAll.ServiceHomeActivity");
        deliverAllClassList.add(resourceFilePath + "activity_service_home");
        deliverAllClassList.add(actPath + "deliverAll.SignUpActivity");
        deliverAllClassList.add(resourceFilePath + "activity_sign_up");
        deliverAllClassList.add(actPath + "deliverAll.TrackOrderActivity");
        deliverAllClassList.add(resourceFilePath + "activity_track_order");
        deliverAllClassList.add("com.realmModel.Options");
        deliverAllClassList.add("com.realmModel.Topping");
        deliverAllClassList.add("com.realmModel.Cart");
        deliverAllClassList.add("com.viewholder.RestaurntCataChildViewHolder");
        deliverAllClassList.add("com.viewholder.RestaurntCataParentViewHolder");
        deliverAllClassList.add("com.viewholder.BiodataExpandable");
        deliverAllClassList.add("com.viewholder.ChildViewHolder");
        deliverAllClassList.add("com.viewholder.ParentViewHolder");
        deliverAllClassList.add("com.model.RestaurantCataChildModel");
        deliverAllClassList.add("com.model.RestaurantCataParentModel");
        deliverAllClassList.add("com.adapter.files.deliverAll.MultiItemOptionAddonPagerAdapter");
        deliverAllClassList.add(resourceFilePath + "item_basket_option_addon");
        deliverAllClassList.add("com.adapter.files.deliverAll.ActiveOrderAdapter");
        deliverAllClassList.add(resourceFilePath + "item_list_orders");
        deliverAllClassList.add("com.adapter.files.deliverAll.CuisinesAdapter");
        deliverAllClassList.add(resourceFilePath + "item_cuisines");
        deliverAllClassList.add("com.adapter.files.deliverAll.CuisinesAdapter24");
        deliverAllClassList.add(resourceFilePath + "item_cuisines24");
        deliverAllClassList.add("com.adapter.files.deliverAll.ExpandableRecyclerAdapter");
        deliverAllClassList.add("com.adapter.files.deliverAll.ExpandableRecyclerAdapterHelper");
        deliverAllClassList.add("com.adapter.files.deliverAll.FoodSearchAdapter");
        deliverAllClassList.add(resourceFilePath + "item_food_search_design");
        deliverAllClassList.add("com.adapter.files.deliverAll.MenuAdapter");
        deliverAllClassList.add(resourceFilePath + "item_menu");
        deliverAllClassList.add("com.adapter.files.deliverAll.RestaurantAdapter");
        deliverAllClassList.add(resourceFilePath + "item_restaurant_list_design");
        deliverAllClassList.add(resourceFilePath + "item_restaurant_list_design_new_vertical_23");
        deliverAllClassList.add("com.adapter.files.deliverAll.RecommendedListAdapter");
        deliverAllClassList.add(resourceFilePath + "recommended_item_list_design");
        deliverAllClassList.add("com.adapter.files.deliverAll.RestaurantmenuAdapter");
        deliverAllClassList.add(resourceFilePath + "item_menu_headerview");
        deliverAllClassList.add(resourceFilePath + "item_resmenu_gridview");
        deliverAllClassList.add(resourceFilePath + "item_menu_list");
        deliverAllClassList.add("com.adapter.files.deliverAll.RestaurantRecomMenuAdapter");
        deliverAllClassList.add(resourceFilePath + "item_resmenu_gridview");
        deliverAllClassList.add("com.adapter.files.deliverAll.RestaurantSearchAdapter");
        deliverAllClassList.add(resourceFilePath + "item_restaurant_list_search_design");
        deliverAllClassList.add("com.adapter.files.deliverAll.ServiceHomeAdapter");
        deliverAllClassList.add(resourceFilePath + "item_service_banner_design");
        deliverAllClassList.add("com.adapter.files.deliverAll.TrackOrderAdapter");
        deliverAllClassList.add(resourceFilePath + "track_order_item_design");
        deliverAllClassList.add("com.fragments.OrderFragment");
        classParams.put("DELIVER_ALL", "No");
        for (String item : deliverAllClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("DELIVER_ALL", "Yes");
                break;
            }
        }
        ArrayList<String> storeIndividualDayAvailabilityClassList = new ArrayList<>();
        storeIndividualDayAvailabilityClassList.add(resourceFilePath + "design_opening_hr_cell");
        classParams.put("STORE_INDIVIDUALDAY_AVAILABILITY_MODULE", "No");
        for (String item : storeIndividualDayAvailabilityClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("STORE_INDIVIDUALDAY_AVAILABILITY_MODULE", "Yes");
                break;
            }
        }

        ArrayList<String> itemNameWiseSearchClassList = new ArrayList<>();
        itemNameWiseSearchClassList.add(drawableFileFilePath + "ic_star_color1_24dp");
        classParams.put("STORE_SEARCH_BY_ITEM_NAME_MODULE", "No");
        for (String item : itemNameWiseSearchClassList) {
            if ((item.startsWith(drawableFileFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(drawableFileFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("STORE_SEARCH_BY_ITEM_NAME_MODULE", "Yes");
                break;
            }
        }


        ArrayList<String> safetyClassList = new ArrayList<>();
        safetyClassList.add("com.general.files.SafetyDialog");
        safetyClassList.add(resourceFilePath + "dailog_safety_measure");
        classParams.put("SAFETY_MODULE", "No");
        for (String item : safetyClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("SAFETY_MODULE", "Yes");
                break;
            }
        }

        ArrayList<String> safetyRatingClassList = new ArrayList<>();
        safetyRatingClassList.add("com.general.files.CustomHorizontalScrollView");
        safetyRatingClassList.add("com.general.files.OnSwipeTouchListener");
        safetyRatingClassList.add("com.general.files.SlideAnimationUtil");
        classParams.put("SAFETY_RATING_MODULE", "No");
        for (String item : safetyRatingClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("SAFETY_RATING_MODULE", "Yes");
                break;
            }
        }


        ArrayList<String> safetyCheckListClassList = new ArrayList<>();
        safetyCheckListClassList.add(resourcePath + "desgin_passenger_limit");
        safetyCheckListClassList.add(drawableFileFilePath + "ic_profile_with_mask.png");
        classParams.put("SAFETY_CHECK_LIST_MODULE", "No");
        for (String item : safetyCheckListClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || (item.startsWith(drawableFileFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(drawableFileFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("SAFETY_RATING_MODULE", "Yes");
                break;
            }
        }


        ArrayList<String> faceMaskVerificationClassList = new ArrayList<>();
        faceMaskVerificationClassList.add(resourcePath + "desgin_mask_verification");
        classParams.put("SAFETY_FACEMASK_VERIFICATION_MODULE", "No");
        for (String item : faceMaskVerificationClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("SAFETY_FACEMASK_VERIFICATION_MODULE", "Yes");
                break;
            }
        }


        ArrayList<String> EighteenPlusFeatureClassList = new ArrayList<>();
        EighteenPlusFeatureClassList.add(resourcePath + "design_upload_service_pic");
        EighteenPlusFeatureClassList.add(resourcePath + "proof_dialog_design");
        classParams.put("EIGHTEEN_PLUS_FEATURE_MODULE", "No");
        for (String item : EighteenPlusFeatureClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("EIGHTEEN_PLUS_FEATURE_MODULE", "Yes");
                break;
            }
        }

        ArrayList<String> ManualTollFeatureClassList = new ArrayList<>();
        ManualTollFeatureClassList.add(resourcePath + "design_upload_service_pic");
        ManualTollFeatureClassList.add(resourcePath + "proof_dialog_design");
        classParams.put("MANUAL_TOLL_FEATURE_MODULE", "No");
        for (String item : ManualTollFeatureClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("MANUAL_TOLL_FEATURE_MODULE", "Yes");
                break;
            }
        }
        ArrayList<String> genieClassList = new ArrayList<>();
        genieClassList.add(actPath + "deliverAll.BuyAnythingActivity");
        genieClassList.add(resourceFilePath + "activity_buy_anything");
        genieClassList.add(resourceFilePath + "bill_details_genie");
        genieClassList.add(resourceFilePath + "design_add_item");
        genieClassList.add(resourceFilePath + "dailog_buyanything_deatils");
        genieClassList.add(actPath + "deliverAll.FindStoreActivity");
        genieClassList.add(resourceFilePath + "activity_find_store");
        genieClassList.add(actPath + "deliverAll.GenieDeliveryHomeActivity");
        genieClassList.add(resourceFilePath + "activity_genie_delivery_home");
        genieClassList.add(resourceFilePath + "view_bill_genie");
        genieClassList.add(resourceFilePath + "review_item_genie");
        genieClassList.add(resourceFilePath + "selectstoreinfo");
        genieClassList.add(resourceFilePath + "deliverall_design_toolbar_new");
        genieClassList.add(resourceFilePath + "confirm_item_genie");
        genieClassList.add(resourceFilePath + "bill_item_genie");
        classParams.put("GENIE_FEATURE_MODULE", "No");
        for (String item : genieClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("GENIE_FEATURE_MODULE", "Yes");
                break;
            }
        }

        ArrayList<String> contactLessDeliveryClassList = new ArrayList<>();
        contactLessDeliveryClassList.add("com.adapter.files.MoreInstructionAdapter");
        contactLessDeliveryClassList.add(actPath + "UserPrefrenceActivity");
        contactLessDeliveryClassList.add(resourceFilePath + "activity_user_prefrence");
        contactLessDeliveryClassList.add(resourceFilePath + "item_instructions");
        classParams.put("CONTACTLESS_DELIVERY_MODULE", "No");
        for (String item : contactLessDeliveryClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("CONTACTLESS_DELIVERY_MODULE", "Yes");
                break;
            }
        }

        ArrayList<String> MultiSingleStoreFeatureClassList = new ArrayList<>();
        MultiSingleStoreFeatureClassList.add("com.fragments.RestaurantAllDetailsNewFragment");
        // MultiSingleStoreFeatureClassList.add(resourceFilePath + "activity_restaurant_all_details_new");
        MultiSingleStoreFeatureClassList.add("com.fragments.FoodDeliveryHomeFragment");
        classParams.put("MULTI_SINGLE_STORE_MODULE", "No");
        for (String item : MultiSingleStoreFeatureClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("MULTI_SINGLE_STORE_MODULE", "Yes");
                break;
            }
        }

        ArrayList<String> categoryWiseStoreFeatureClassList = new ArrayList<>();
        categoryWiseStoreFeatureClassList.add("com.adapter.files.deliverAll.MainCategoryAdapter");
        categoryWiseStoreFeatureClassList.add(resourceFilePath + "item_category_child");
        categoryWiseStoreFeatureClassList.add(resourceFilePath + "item_restaurant_list_header_design_23");
        categoryWiseStoreFeatureClassList.add(resourceFilePath + "item_restaurant_list_header_design_24");
        categoryWiseStoreFeatureClassList.add("com.adapter.files.deliverAll.RestaurantChildAdapter");
        categoryWiseStoreFeatureClassList.add(resourceFilePath + "item_childrestaurant_list_design");
        categoryWiseStoreFeatureClassList.add(resourceFilePath + "item_childrestaurant_list_design_23");
        categoryWiseStoreFeatureClassList.add(resourceFilePath + "main_category_wise_store");
        categoryWiseStoreFeatureClassList.add("com.model.MainCategoryModel");

        categoryWiseStoreFeatureClassList.add("com.adapter.files.deliverAll.StoreCategoryAdapter24");
        categoryWiseStoreFeatureClassList.add(resourceFilePath + "item_store_category24");
        categoryWiseStoreFeatureClassList.add("com.adapter.files.deliverAll.StoreAdapter24");
        categoryWiseStoreFeatureClassList.add(resourceFilePath + "item_store_grid_24");
        categoryWiseStoreFeatureClassList.add(resourceFilePath + "item_store_list_24");
        classParams.put("CATEGORY_WISE_STORE_MODULE", "No");
        for (String item : categoryWiseStoreFeatureClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("CATEGORY_WISE_STORE_MODULE", "Yes");
                break;
            }
        }

        ArrayList<String> multiDeliveryClassList = new ArrayList<>();
        multiDeliveryClassList.add(actPath + "EnterMultiDeliveryDetailsActivity");
        multiDeliveryClassList.add(resourceFilePath + "activity_multi_delivery_detail");
        multiDeliveryClassList.add(actPath + "MultiDeliverySecondPhaseActivity");
        multiDeliveryClassList.add(resourceFilePath + "activity_multi_second_phase");
        multiDeliveryClassList.add(actPath + "MultiDeliveryThirdPhaseActivity");
        multiDeliveryClassList.add(resourceFilePath + "activity_multi_third_phase_multi");
        multiDeliveryClassList.add(actPath + "ViewMultiDeliveryDetailsActivity");
        multiDeliveryClassList.add(resourceFilePath + "activity_multi_delivery_details");
        multiDeliveryClassList.add("com.model.Delivery_Data");
        multiDeliveryClassList.add("com.model.Multi_Delivery_Data");
        multiDeliveryClassList.add("com.model.Multi_Dest_Info_Detail_Data");
        multiDeliveryClassList.add("com.model.Trip_Status");
        multiDeliveryClassList.add("com.adapter.files.MultiDestinationItemAdapter");
        multiDeliveryClassList.add(resourceFilePath + "multi_dest_item_layout");
        multiDeliveryClassList.add("com.adapter.files.MultiListViewAdapter");
        multiDeliveryClassList.add(resourceFilePath + "design_view_multi_delivery_detail");
        multiDeliveryClassList.add("com.adapter.files.ViewMultiDeliveryDetailRecyclerAdapter");
        multiDeliveryClassList.add(resourceFilePath + "multi_delivery_details_design");
        multiDeliveryClassList.add("com.adapter.files.MultiDeliveryDetailAdapter");
        multiDeliveryClassList.add("com.adapter.files.MultiPaymentTypeRecyclerAdapter");
        multiDeliveryClassList.add(resourceFilePath + "multi_item_selected_payment_method");
        multiDeliveryClassList.add("com.fragments.MultiScrollSupportMapFragment");
        multiDeliveryClassList.add("com.general.files.MapComparator");
        multiDeliveryClassList.add("com.general.files.DataParser");
        multiDeliveryClassList.add("com.general.files.TextWatcherExtendedListener");
        multiDeliveryClassList.add("com.general.files.CustomLinearLayoutManager");

        classParams.put("MULTI_DELIVERY", "No");
        for (String item : multiDeliveryClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("MULTI_DELIVERY", "Yes");
                break;
            }
        }


        ArrayList<String> uberXGeneralClassList = new ArrayList<>();
        uberXGeneralClassList.add(actPath + "UberXActivity");
        uberXGeneralClassList.add(resourceFilePath + "activity_uber_x");
        uberXGeneralClassList.add("com.adapter.files.UberXCategoryAdapter");
        uberXGeneralClassList.add(resourceFilePath + "item_rdu_banner_design");
        uberXGeneralClassList.add(resourceFilePath + "item_uberx_cat_grid_design");
        uberXGeneralClassList.add(resourceFilePath + "item_uberx_cat_list_design");
        uberXGeneralClassList.add("com.adapter.files.UberXBannerPagerAdapter");
        uberXGeneralClassList.add(resourceFilePath + "item_uber_x_banner_design");


        classParams.put("UBERX_GENERAL_SERVICE", "No");
        for (String item : uberXGeneralClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                Logger.e("EXIST_FILE", "::" + item);
                classParams.put("UBERX_GENERAL_SERVICE", "Yes");
                break;
            }
        }

        ArrayList<String> uberXClassList = new ArrayList<>();

        uberXClassList.add(actPath + "UberxCartActivity");
        uberXClassList.add(resourceFilePath + "activity_uberx_cart");
        uberXClassList.add(actPath + "UberxFilterActivity");
        uberXClassList.add(resourceFilePath + "activity_uberx_filter");
        uberXClassList.add(actPath + "UberXSelectServiceActivity");
        uberXClassList.add(resourceFilePath + "activity_uber_xselect_service");
        uberXClassList.add(actPath + "UfxPaymentActivity");
        uberXClassList.add(actPath + "CarWashBookingDetailsActivity");
        uberXClassList.add(resourceFilePath + "activity_car_wash_booking_details");
        uberXClassList.add(resourceFilePath + "item_uberxcheckout_row");
        uberXClassList.add(actPath + "MoreInfoActivity");
        uberXClassList.add(resourceFilePath + "activity_more_info");
        uberXClassList.add(actPath + "ProviderInfoActivity");
        uberXClassList.add(resourceFilePath + "activity_provider_info");
        uberXClassList.add(actPath + "MoreServiceInfoActivity");
        uberXClassList.add(resourceFilePath + "activity_more_service_info");
        uberXClassList.add(actPath + "ScheduleDateSelectActivity");
        uberXClassList.add(resourceFilePath + "activity_schedule_date_select");
        uberXClassList.add("com.adapter.files.DatesRecyclerAdapter");
        uberXClassList.add(resourceFilePath + "item_dates_design");
        uberXClassList.add("com.fragments.ServiceFragment");
        uberXClassList.add(resourceFilePath + "fragment_services");
        uberXClassList.add("com.fragments.ReviewsFragment");
        uberXClassList.add(resourceFilePath + "fragment_reviews");
        uberXClassList.add("com.fragments.GalleryFragment");
        uberXClassList.add(resourceFilePath + "fragment_gallery");
        uberXClassList.add("com.fragments.PaymentFrag");
        //  uberXClassList.add("com.adapter.files.GalleryImagesRecyclerAdapter");
        //  uberXClassList.add(resourceFilePath + "item_gallery_list");
        uberXClassList.add("com.adapter.files.UberXOnlineDriverListAdapter");
        uberXClassList.add(resourceFilePath + "item_online_driver_list_design");

        uberXClassList.add("com.adapter.files.UberXHomeActBannerAdapter");
        uberXClassList.add("com.adapter.files.TowTruckVehicleAdpater");
        uberXClassList.add(resourceFilePath + "item_towtruck_vehicle_list_design");
        uberXClassList.add("com.adapter.files.TimeSlotAdapter");
        uberXClassList.add(resourceFilePath + "item_timeslot_view");
        uberXClassList.add("com.realmModel.CarWashCartData");
        uberXClassList.add("com.adapter.files.PinnedCategorySectionListAdapter");
        uberXClassList.add(resourceFilePath + "service_list_item");
        uberXClassList.add("com.adapter.files.DriverFeedbackRecycleAdapter");
        uberXClassList.add(resourceFilePath + "item_feedback_design");

        classParams.put("UBERX_SERVICE", "No");
        for (String item : uberXClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                Logger.e("EXIST_FILE", "::" + item);
                classParams.put("UBERX_SERVICE", "Yes");
                break;
            }
        }

        ArrayList<String> onGoingJobsClassList = new ArrayList<>();
        onGoingJobsClassList.add(actPath + "OnGoingTripsActivity");
        onGoingJobsClassList.add(resourceFilePath + "activity_ongoingtrips_layout");
        onGoingJobsClassList.add(actPath + "OnGoingTripDetailsActivity");
        onGoingJobsClassList.add(resourceFilePath + "layout_ongoing_trip_details");
        onGoingJobsClassList.add("com.adapter.files.OngoingTripAdapter");
        onGoingJobsClassList.add(resourceFilePath + "item_ongoing_trips_detail");
        onGoingJobsClassList.add("com.adapter.files.OnGoingTripDetailAdapter");
        onGoingJobsClassList.add(resourceFilePath + "item_design_ongoing_trip_cell");
        onGoingJobsClassList.add("com.general.files.CustomSupportMapFragment");

        classParams.put("ON_GOING_JOB_SECTION", "No");
        for (String item : onGoingJobsClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("ON_GOING_JOB_SECTION", "Yes");
                break;
            }
        }

        ArrayList<String> commonDeliveryTypeClassList = new ArrayList<>();
        ArrayList<String> commonDeliveryTypeClassList_tmp = new ArrayList<>();
        commonDeliveryTypeClassList.add(actPath + "CommonDeliveryTypeSelectionActivity");
        commonDeliveryTypeClassList.add(resourceFilePath + "activity_multi_type_selection");
        // commonDeliveryTypeClassList.add("com.general.files.OpenCatType");
        commonDeliveryTypeClassList.add("com.adapter.files.DeliveryBannerAdapter");
        commonDeliveryTypeClassList.add(resourceFilePath + "item_delivery_banner_design");
        commonDeliveryTypeClassList.add("com.adapter.files.DeliveryIconAdapter");
        commonDeliveryTypeClassList.add(resourceFilePath + "delivery_icon_layout");
        commonDeliveryTypeClassList.add("com.adapter.files.SubCategoryItemAdapter");
        commonDeliveryTypeClassList.add(resourceFilePath + "item_icon_layout");
        commonDeliveryTypeClassList.add("com.model.DeliveryIconDetails");
        commonDeliveryTypeClassList.add(resourceFilePath + "activity_food_delivery_home");
        commonDeliveryTypeClassList.add(resourceFilePath + "activity_food_delivery_home_23");

        commonDeliveryTypeClassList.add("com.fragments.deliverall.FoodHomeScreen");
        commonDeliveryTypeClassList.add("com.fragments.deliverall.FoodDeliveryHomeFragment24");
        commonDeliveryTypeClassList.add(resourceFilePath + "activity_food_delivery_home_24");
        commonDeliveryTypeClassList_tmp.addAll(commonDeliveryTypeClassList);

        classParams.put("COMMON_DELIVERY_TYPE_SECTION", "No");
        for (String item : commonDeliveryTypeClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("COMMON_DELIVERY_TYPE_SECTION", "Yes");
            } else {
                commonDeliveryTypeClassList_tmp.remove(item);
            }
        }
        commonDeliveryTypeClassList.clear();
        commonDeliveryTypeClassList.addAll(commonDeliveryTypeClassList_tmp);

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

        ArrayList<String> businessProfileClassList = new ArrayList<>();
        businessProfileClassList.add(actPath + "SelectOrganizationActivity");
        businessProfileClassList.add(resourceFilePath + "activity_organization_list");
        businessProfileClassList.add(actPath + "OrganizationActivity");
        businessProfileClassList.add(resourceFilePath + "activity_organization");
        businessProfileClassList.add(actPath + "MyBusinessProfileActivity");
        businessProfileClassList.add(resourceFilePath + "activity_my_business_profile");
        businessProfileClassList.add(actPath + "BusinessSetupActivity");
        businessProfileClassList.add(resourceFilePath + "activity_business_setup");
        businessProfileClassList.add(actPath + "BusinessProfileActivity");
        businessProfileClassList.add(resourceFilePath + "activity_business_profile");
        businessProfileClassList.add(actPath + "BusinessSelectPaymentActivity");
        businessProfileClassList.add(resourceFilePath + "activity_business_select_payment");
        businessProfileClassList.add("com.fragments.BusinessProfileIntroFragment");
        businessProfileClassList.add(resourceFilePath + "fragment_business_profile_intro");
        businessProfileClassList.add("com.fragments.BusinessProfileListFragment");
        businessProfileClassList.add(resourceFilePath + "fragment_business_profile_list");
        businessProfileClassList.add("com.adapter.files.OrganizationPinnedSectionListAdapter");
        businessProfileClassList.add(resourceFilePath + "organization_list_item");
        businessProfileClassList.add("com.adapter.files.OrganizationListItem");

        classParams.put("BUSINESS_PROFILE_FEATURE", "No");
        for (String item : businessProfileClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("BUSINESS_PROFILE_FEATURE", "Yes");
                break;
            }
        }


        ArrayList<String> deliveryModuleClassList = new ArrayList<>();
        deliveryModuleClassList.add(actPath + "EnterDeliveryDetailsActivity");
        deliveryModuleClassList.add(resourceFilePath + "activity_enter_delivery_details");
        deliveryModuleClassList.add("com.model.DeliveryDetails");

        classParams.put("DELIVERY_MODULE", "No");
        for (String item : deliveryModuleClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("DELIVERY_MODULE", "Yes");
                break;
            }
        }

        ArrayList<String> rideModuleClassList = new ArrayList<>();
        rideModuleClassList.add("com.fragments.DriverAssignedHeaderFragment");
        rideModuleClassList.add(resourceFilePath + "fragment_driver_assigned_header");
        rideModuleClassList.add("com.fragments.DriverDetailFragment");
        rideModuleClassList.add(resourceFilePath + "fragment_driver_detail");

        classParams.put("RIDE_SECTION", "No");
        for (String item : rideModuleClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("RIDE_SECTION", "Yes");
                break;
            }
        }

        ArrayList<String> rduModuleClassList = new ArrayList<>();
        rduModuleClassList.add(actPath + "MainActivity");
        rduModuleClassList.add(resourceFilePath + "activity_main");
        rduModuleClassList.add(resourceFilePath + "activity_prefrance");
        rduModuleClassList.add(actPath + "RatingActivity");
        rduModuleClassList.add(resourceFilePath + "activity_rating");
        rduModuleClassList.add(actPath + "MyBookingsActivity");
        rduModuleClassList.add(resourceFilePath + "activity_my_bookings");
        rduModuleClassList.add(actPath + "HistoryActivity");
        rduModuleClassList.add(resourceFilePath + "activity_history");
        rduModuleClassList.add(actPath + "HistoryDetailActivity");
        rduModuleClassList.add(resourceFilePath + "activity_history_detail");
        rduModuleClassList.add(actPath + "ConfirmEmergencyTapActivity");
        rduModuleClassList.add(resourceFilePath + "activity_confirm_emergency_tap");
        rduModuleClassList.add(actPath + "EmergencyContactActivity");
        rduModuleClassList.add(resourceFilePath + "activity_emergency_contact");
        rduModuleClassList.add(actPath + "ChatActivity");
        rduModuleClassList.add(resourceFilePath + "design_trip_chat_detail_dialog");
        rduModuleClassList.add("com.fragments.BookingFragment");
        //    rduModuleClassList.add(resourceFilePath + "fragment_booking");
        rduModuleClassList.add("com.fragments.CabSelectionFragment");
        rduModuleClassList.add(resourceFilePath + "fragment_new_cab_selection");
        rduModuleClassList.add(resourceFilePath + "input_box_view");
        rduModuleClassList.add(resourceFilePath + "custom_marker");
        rduModuleClassList.add(resourceFilePath + "dailog_faredetails");
        rduModuleClassList.add("com.fragments.HistoryFragment");
        //     rduModuleClassList.add(resourceFilePath + "fragment_booking");
        rduModuleClassList.add("com.fragments.MainHeaderFragment");
        rduModuleClassList.add(resourceFilePath + "fragment_main_header");
        rduModuleClassList.add("com.adapter.files.CabTypeAdapter");
        rduModuleClassList.add(resourceFilePath + "item_design_cab_type");
        rduModuleClassList.add(resourceFilePath + "item_design_vertical_cab_type");
        rduModuleClassList.add("com.adapter.files.ChatMessagesRecycleAdapter");
        rduModuleClassList.add(resourceFilePath + "message");
        rduModuleClassList.add("com.adapter.files.EmergencyContactRecycleAdapter");
        rduModuleClassList.add(resourceFilePath + "emergency_contact_item");
        rduModuleClassList.add("com.adapter.files.HistoryRecycleAdapter");
        rduModuleClassList.add(resourceFilePath + "item_history_design");
        rduModuleClassList.add("com.adapter.files.MyBookingsRecycleAdapter");
        rduModuleClassList.add(resourceFilePath + "item_my_bookings_design");
        rduModuleClassList.add("com.adapter.files.RequestPickUpAdapter");
        rduModuleClassList.add(resourceFilePath + "item_design_request_pick_up");
        rduModuleClassList.add("com.adapter.files.UberXBannerAdapter");
        rduModuleClassList.add(resourceFilePath + "item_rdu_banner_design");
        rduModuleClassList.add("com.general.files.LoadAvailableCab");
        rduModuleClassList.add("com.dialogs.RequestNearestCab");

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


        ArrayList<String> bookForSomeoneModuleClassList = new ArrayList<>();
        bookForSomeoneModuleClassList.add("com.adapter.files.BookSomeOneContactListAdapter");
        bookForSomeoneModuleClassList.add("com.model.ContactModel");
        bookForSomeoneModuleClassList.add(resourceFilePath + "design_book_someone_details");
        bookForSomeoneModuleClassList.add(resourceFilePath + "item_book_someone_contact_design");
        bookForSomeoneModuleClassList.add(resourceFilePath + "item_book_someone_contacts_header");

        classParams.put("BOOK_FOR_ELSE_SECTION", "No");
        for (String item : bookForSomeoneModuleClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("BOOK_FOR_ELSE_SECTION", "Yes");
                break;
            }
        }


        ArrayList<String> favDriverModuleClassList = new ArrayList<>();
        favDriverModuleClassList.add(actPath + "FavouriteDriverActivity");
        favDriverModuleClassList.add(resourceFilePath + "activity_favorite_driver");
        favDriverModuleClassList.add("com.adapter.files.FavoriteDriverAdapter");
        favDriverModuleClassList.add("com.fragments.FavDriverFragment");
        favDriverModuleClassList.add(resourceFilePath + "fragment_fav_driver");
        favDriverModuleClassList.add(resourceFilePath + "item_fav_driver_design");
        favDriverModuleClassList.add(resourceFilePath + "item_fav_driver_heder_design");
        favDriverModuleClassList.add("com.general.files.favDriverComparator");

        classParams.put("FAV_DRIVER_SECTION", "No");
        for (String item : favDriverModuleClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("FAV_DRIVER_SECTION", "Yes");
                break;
            }
        }

        ArrayList<String> stopOverPointModuleClassList = new ArrayList<>();
        stopOverPointModuleClassList.add("com.adapter.files.StopOverPointsAdapter");
        stopOverPointModuleClassList.add("com.general.files.StopOverComparator");
        stopOverPointModuleClassList.add("com.general.files.StopOverPointsDataParser");
        stopOverPointModuleClassList.add("com.model.Stop_Over_Points_Data");
        stopOverPointModuleClassList.add(resourceFilePath + "design_stopover_locations");

        classParams.put("STOP_OVER_POINT_SECTION", "No");
        for (String item : stopOverPointModuleClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("STOP_OVER_POINT_SECTION", "Yes");
                break;
            }
        }


        ArrayList<String> donationModuleClassList = new ArrayList<>();
        donationModuleClassList.add("com.adapter.files.DonationBannerAdapter");
        donationModuleClassList.add(resourceFilePath + "item_donation_banner_design");
        donationModuleClassList.add(actPath + "DonationActivity");
        donationModuleClassList.add(resourceFilePath + "activity_donation");
        classParams.put("DONATION_SECTION", "No");
        for (String item : donationModuleClassList) {
            if ((item.startsWith(resourceFilePath) && MyApp.getInstance().getApplicationContext() != null && Utils.isResourceFileExist(MyApp.getInstance().getApplicationContext(), item.replace(resourceFilePath, ""), resourcePath)) || Utils.isClassExist(item)) {
                classParams.put("DONATION_SECTION", "Yes");
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
        liveChatClassList.add("AGo to pp's Level build.Gradle File and Remove Library 'com.github.livechat:chat-window-android'");
        tollModuleClassList.add("Remove Declaration of Toll URL from CommonUtilities File And remove portion of Toll cost from code. (Remove Network execution of toll URL)");
        /** Removal file of libraries **/

        if (classParams.get("SAFETY_TOOLS_MODULE") != null && classParams.get("SAFETY_TOOLS_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("SAFETY_TOOLS_MODULE_FILES", android.text.TextUtils.join(",", safetyToolsClassList));
        }

        if (classParams.get("BID_SERVICE") != null && classParams.get("BID_SERVICE").equalsIgnoreCase("Yes")) {
            classParams.put("BID_SERVICE_FILES", android.text.TextUtils.join(",", biddingClassList));
        }

        if (classParams.get("INTERCITY_MODULE") != null && classParams.get("INTERCITY_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("INTERCITY_MODULE_FILES", android.text.TextUtils.join(",", interCityClassList));
        }

        if (classParams.get("VOIP_SERVICE") != null && classParams.get("VOIP_SERVICE").equalsIgnoreCase("Yes")) {
            classParams.put("VOIP_SERVICE_FILES", android.text.TextUtils.join(",", voipServiceClassList));
        }

        if (classParams.get("ADVERTISEMENT_MODULE") != null && classParams.get("ADVERTISEMENT_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("ADVERTISEMENT_MODULE_FILES", android.text.TextUtils.join(",", advertisementClassList));
        }

        if (classParams.get("LIVE_ACTIVITY_MODULE") != null && classParams.get("LIVE_ACTIVITY_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("LIVE_ACTIVITY_MODULE_FILES", android.text.TextUtils.join(",", liveActivityClassList));
        }

        if (classParams.get("GIFTCARD_MODULE") != null && classParams.get("GIFTCARD_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("GIFTCARD_MODULE_FILES", android.text.TextUtils.join(",", giftCardClassList));
        }
        if (classParams.get("NEARBY_MODULE") != null && classParams.get("NEARBY_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("NEARBY_MODULE_FILES", android.text.TextUtils.join(",", nearByServiceClassList));
        }
        if (classParams.get("RENT_ITEM_SERVICE") != null && classParams.get("RENT_ITEM_SERVICE").equalsIgnoreCase("Yes")) {
            classParams.put("RENT_ITEM_SERVICE_FILES", android.text.TextUtils.join(",", rentItemClassList));
        }
        if (classParams.get("PARKING_MODULE") != null && classParams.get("PARKING_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("PARKING_MODULE_FILES", android.text.TextUtils.join(",", parkingClassList));
        }
        if (classParams.get("RIDE_SHARE_PRO_MODULE") != null && classParams.get("RIDE_SHARE_PRO_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("RIDE_SHARE_PRO_MODULE_FILES", android.text.TextUtils.join(",", proRideSharingClassList));
        }
        if (classParams.get("TRACKING_MODULE") != null && classParams.get("TRACKING_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("TRACKING_MODULE_FILES", android.text.TextUtils.join(",", trackServiceClassList));
        }

        if (classParams.get("LINKEDIN_MODULE") != null && classParams.get("LINKEDIN_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("LINKEDIN_MODULE_FILES", android.text.TextUtils.join(",", linkedInClassList));
        }

        if (classParams.get("POOL_MODULE") != null && classParams.get("POOL_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("POOL_MODULE_FILES", android.text.TextUtils.join(",", poolClassList));
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

        if (classParams.get("DELIVER_ALL_GENERAL") != null && classParams.get("DELIVER_ALL_GENERAL").equalsIgnoreCase("Yes")) {
            classParams.put("DELIVER_ALL_GENERAL_FILES", android.text.TextUtils.join(",", deliverAllGeneralClassList));
        }

        if (classParams.get("MULTI_DELIVERY") != null && classParams.get("MULTI_DELIVERY").equalsIgnoreCase("Yes")) {
            classParams.put("MULTI_DELIVERY_FILES", android.text.TextUtils.join(",", multiDeliveryClassList));
        }

        if (classParams.get("UBERX_SERVICE") != null && classParams.get("UBERX_SERVICE").equalsIgnoreCase("Yes")) {
            classParams.put("UBERX_FILES", android.text.TextUtils.join(",", uberXClassList));
        }
        if (classParams.get("UBERX_GENERAL_SERVICE") != null && classParams.get("UBERX_GENERAL_SERVICE").equalsIgnoreCase("Yes")) {
            classParams.put("UBERX_GENERAL_SERVICE_FILES", android.text.TextUtils.join(",", uberXGeneralClassList));
        }

        if (classParams.get("ON_GOING_JOB_SECTION") != null && classParams.get("ON_GOING_JOB_SECTION").equalsIgnoreCase("Yes")) {
            classParams.put("ON_GOING_JOB_SECTION_FILES", android.text.TextUtils.join(",", onGoingJobsClassList));
        }

        if (classParams.get("COMMON_DELIVERY_TYPE_SECTION") != null && classParams.get("COMMON_DELIVERY_TYPE_SECTION").equalsIgnoreCase("Yes")) {
            classParams.put("COMMON_DELIVERY_TYPE_SECTION_FILES", android.text.TextUtils.join(",", commonDeliveryTypeClassList));
        }

        if (classParams.get("NEWS_SECTION") != null && classParams.get("NEWS_SECTION").equalsIgnoreCase("Yes")) {
            classParams.put("NEWS_SERVICE_FILES", android.text.TextUtils.join(",", newsClassList));
        }

        if (classParams.get("RENTAL_FEATURE") != null && classParams.get("RENTAL_FEATURE").equalsIgnoreCase("Yes")) {
            classParams.put("RENTAL_SERVICE_FILES", android.text.TextUtils.join(",", rentalClassList));
        }

        if (classParams.get("BUSINESS_PROFILE_FEATURE") != null && classParams.get("BUSINESS_PROFILE_FEATURE").equalsIgnoreCase("Yes")) {
            classParams.put("BUSINESS_PROFILE_FILES", android.text.TextUtils.join(",", businessProfileClassList));
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

        if (classParams.get("BOOK_FOR_ELSE_SECTION") != null && classParams.get("BOOK_FOR_ELSE_SECTION").equalsIgnoreCase("Yes")) {
            classParams.put("BOOK_FOR_ELSE_SECTION_FILES", android.text.TextUtils.join(",", bookForSomeoneModuleClassList));
        }

        if (classParams.get("STORE_INDIVIDUALDAY_AVAILABILITY_MODULE") != null && classParams.get("STORE_INDIVIDUALDAY_AVAILABILITY_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("STORE_INDIVIDUALDAY_AVAILABILITY_MODULE_FILES", android.text.TextUtils.join(",", storeIndividualDayAvailabilityClassList));
        }

        if (classParams.get("STORE_SEARCH_BY_ITEM_NAME_MODULE") != null && classParams.get("STORE_SEARCH_BY_ITEM_NAME_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("STORE_SEARCH_BY_ITEM_NAME_MODULE_FILES", android.text.TextUtils.join(",", itemNameWiseSearchClassList));
        }

        if (classParams.get("SAFETY_MODULE") != null && classParams.get("SAFETY_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("SAFETY_MODULE_FILES", android.text.TextUtils.join(",", safetyClassList));
        }

        if (classParams.get("SAFETY_RATING_MODULE") != null && classParams.get("SAFETY_RATING_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("SAFETY_RATING_MODULE_FILES", android.text.TextUtils.join(",", safetyRatingClassList));
        }

        if (classParams.get("SAFETY_CHECK_LIST_MODULE") != null && classParams.get("SAFETY_CHECK_LIST_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("SAFETY_CHECK_LIST_MODULE_FILES", android.text.TextUtils.join(",", safetyCheckListClassList));
        }

        if (classParams.get("SAFETY_FACEMASK_VERIFICATION_MODULE") != null && classParams.get("SAFETY_FACEMASK_VERIFICATION_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("SAFETY_FACEMASK_VERIFICATION_MODULE_FILES", android.text.TextUtils.join(",", faceMaskVerificationClassList));
        }

        if (classParams.get("EIGHTEEN_PLUS_FEATURE_MODULE") != null && classParams.get("EIGHTEEN_PLUS_FEATURE_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("EIGHTEEN_PLUS_FEATURE_MODULE_FILES", android.text.TextUtils.join(",", EighteenPlusFeatureClassList));
        }

        if (classParams.get("MANUAL_TOLL_FEATURE_MODULE") != null && classParams.get("MANUAL_TOLL_FEATURE_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("MANUAL_TOLL_FEATURE_MODULE_FILES", android.text.TextUtils.join(",", ManualTollFeatureClassList));
        }

        if (classParams.get("GENIE_FEATURE_MODULE") != null && classParams.get("GENIE_FEATURE_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("GENIE_FEATURE_MODULE_FILES", android.text.TextUtils.join(",", genieClassList));
        }
        if (classParams.get("CONTACTLESS_DELIVERY_MODULE") != null && classParams.get("CONTACTLESS_DELIVERY_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("CONTACTLESS_DELIVERY_MODULE_FILES", android.text.TextUtils.join(",", contactLessDeliveryClassList));
        }
        if (classParams.get("MULTI_SINGLE_STORE_MODULE") != null && classParams.get("MULTI_SINGLE_STORE_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("MULTI_SINGLE_STORE_MODULE_FILES", android.text.TextUtils.join(",", MultiSingleStoreFeatureClassList));
        }
        if (classParams.get("CATEGORY_WISE_STORE_MODULE") != null && classParams.get("CATEGORY_WISE_STORE_MODULE").equalsIgnoreCase("Yes")) {
            classParams.put("CATEGORY_WISE_STORE_MODULE_FILES", android.text.TextUtils.join(",", categoryWiseStoreFeatureClassList));
        }

        if (classParams.get("FAV_DRIVER_SECTION") != null && classParams.get("FAV_DRIVER_SECTION").equalsIgnoreCase("Yes")) {
            classParams.put("FAV_DRIVER_SECTION_FILES", android.text.TextUtils.join(",", favDriverModuleClassList));
        }

        if (classParams.get("STOP_OVER_POINT_SECTION") != null && classParams.get("STOP_OVER_POINT_SECTION").equalsIgnoreCase("Yes")) {
            classParams.put("STOP_OVER_POINT_SECTION_FILES", android.text.TextUtils.join(",", stopOverPointModuleClassList));
        }


        if (classParams.get("DONATION_SECTION") != null && classParams.get("DONATION_SECTION").equalsIgnoreCase("Yes")) {
            classParams.put("DONATION_SECTION_FILES", android.text.TextUtils.join(",", donationModuleClassList));
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