package com.act.rentItem;

import static android.view.View.GONE;
import static android.view.View.VISIBLE;

import android.annotation.SuppressLint;
import android.content.Context;
import android.content.Intent;
import android.location.Location;
import android.net.Uri;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.viewpager.widget.ViewPager;

import com.act.rentItem.adapter.PhotosAdapter;
import com.activity.ParentActivity;
import com.dialogs.MyCommonDialog;
import com.general.call.CommunicationManager;
import com.general.call.DefaultCommunicationHandler;
import com.general.call.MediaDataProvider;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.like.LikeButton;
import com.map.GeoMapLoader;
import com.map.models.LatLng;
import com.map.models.MarkerOptions;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.viewpagerdotsindicator.DotsIndicator;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class RentItemReviewPostActivity extends ParentActivity implements GeoMapLoader.OnMapReadyCallback {

    private DotsIndicator dotsIndicator;
    private RelativeLayout photosArea, dotsArea;
    private ViewPager photosViewPager;
    private PhotosAdapter photoAdapter;
    private final ArrayList<HashMap<String, String>> mediaList = new ArrayList<>();

    private HashMap<String, String> mRentEditHashMap;
    private int bannerWidth, bannerHeight;
    private MTextView txtPostTitle, txtPostPrice, txtPostDurationStatus, txtPostedTxt, txtPostDescription, locationTxt;
    private LinearLayout pickupTimeSlotContainer, rentDetailsArea, rentItemDetailsContainer;
    private LinearLayout cardPostDescription, pickupAvailibilityArea;

    private MButton callBtnTxt, sendInquiryBtnTxt;
    private View lineArea;
    private boolean isInquiryDone = false, divider_gone = false;
    LinearLayout ll_send_enquiry_btn, ll_call_btn;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_rent_item_review_post);

        mRentEditHashMap = (HashMap<String, String>) getIntent().getSerializableExtra("rentEditHashMap");
        if (mRentEditHashMap == null) {
            return;
        }

        MTextView titleTxt = findViewById(R.id.titleTxt);
        ll_send_enquiry_btn = findViewById(R.id.ll_send_enquiry_btn);
        ll_call_btn = findViewById(R.id.ll_call_btn);
        if (getIntent().getBooleanExtra("isOwnerView", false)) {
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_PRODUCT_DETAILS"));
        } else {
            titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_REVIEW_POST"));
        }
        ImageView receiptImgView = findViewById(R.id.receiptImgView);
        receiptImgView.setVisibility(View.GONE);
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);

        dotsIndicator = findViewById(R.id.dots_indicator);
        dotsArea = findViewById(R.id.dotsArea);

        photosArea = findViewById(R.id.photosArea);
        photosArea.setVisibility(View.GONE);
//        View viewGradient = findViewById(R.id.viewGradient);
//        manageVectorImage(viewGradient, R.drawable.ic_gradient_gray, R.drawable.ic_gradient_gray_compat);

        photosViewPager = findViewById(R.id.photosViewPager);

        bannerWidth = (int) (Utils.getScreenPixelWidth(this));
        bannerHeight = (int) (bannerWidth / 1.77777778);
        photoAdapter = new PhotosAdapter(mediaList, bannerWidth, bannerHeight, (pos, finalImageUrl) -> {
            (new ActUtils(getActContext())).openURL(finalImageUrl);
        }, true, false);
        photosViewPager.setAdapter(photoAdapter);

        txtPostTitle = findViewById(R.id.txtPostTitle);
        txtPostPrice = findViewById(R.id.txtPostPrice);
        txtPostDurationStatus = findViewById(R.id.txtPostDurationStatus);
        pickupAvailibilityArea = findViewById(R.id.pickupAvailibilityArea);
        txtPostedTxt = findViewById(R.id.txtPostedTxt);

        cardPostDescription = findViewById(R.id.cardPostDescription);
        MTextView postDescriptionHTxt = findViewById(R.id.postDescriptionHTxt);
        postDescriptionHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_DESCRIPTION_TXT"));
        txtPostDescription = findViewById(R.id.txtPostDescription);

        ImageView mapBtnImgView = findViewById(R.id.MapBtnImgView);
        ImageView directionBtnImgView = findViewById(R.id.DirectionBtnImgView);
        addToClickHandler(mapBtnImgView);
        addToClickHandler(directionBtnImgView);

        MTextView pickUpAvailabilityHTxt = findViewById(R.id.pickUpAvailabilityHTxt);
        if (mRentEditHashMap.get("eType").equalsIgnoreCase("RentEstate") || mRentEditHashMap.get("eType").equalsIgnoreCase("RentCars")) {
            pickUpAvailabilityHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_ESTATE_AVAILABILTY_TXT"));
        } else {
            pickUpAvailabilityHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_ITEM_PICKUP_AVAILBILITY"));
        }
        pickupTimeSlotContainer = findViewById(R.id.pickupTimeSlotContainer);

        MTextView detailsHTxt = findViewById(R.id.detailsHTxt);
        if (mRentEditHashMap.get("eType").equalsIgnoreCase("RentEstate")) {
            detailsHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_PROPERTY_DETAIL"));
        } else if (mRentEditHashMap.get("eType").equalsIgnoreCase("RentCars")) {
            detailsHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_CAR_DETAILS"));
        } else {
            detailsHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_ITEM_DETAILS"));
        }
        titleTxt.setText(detailsHTxt.getText().toString());
        rentDetailsArea = findViewById(R.id.rentDetailsArea);
        rentItemDetailsContainer = findViewById(R.id.rentItemDetailsContainer);

        MTextView locationHTxt = findViewById(R.id.locationHTxt);
        locationHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_LOCATION_FOR_FRONT"));
        locationTxt = findViewById(R.id.locationTxt);
        addToClickHandler(locationTxt);

        likeAndOwnerView();

        setData();

        (new GeoMapLoader(this, R.id.mapRentContainer)).bindMap(this);
    }

    public Context getActContext() {
        return RentItemReviewPostActivity.this;
    }

    @SuppressLint("PotentialBehaviorOverride")
    @Override
    public void onMapReady(@NonNull GeoMapLoader.GeoMap googleMap) {
        googleMap.getUiSettings().setAllGesturesEnabled(false);
        if (generalFunc.checkLocationPermission(true)) {
            googleMap.setMyLocationEnabled(false);
            googleMap.getUiSettings().setTiltGesturesEnabled(false);
            googleMap.getUiSettings().setZoomControlsEnabled(false);
            googleMap.getUiSettings().setCompassEnabled(false);
            googleMap.getUiSettings().setMyLocationButtonEnabled(false);
        }
        googleMap.setOnMarkerClickListener(marker -> {
            marker.hideInfoWindow();
            return true;
        });

        Location userLocation = new Location("source");
        userLocation.setLatitude(GeneralFunctions.parseDoubleValue(0.0, mRentEditHashMap.get("vLatitude")));
        userLocation.setLongitude(GeneralFunctions.parseDoubleValue(0.0, mRentEditHashMap.get("vLongitude")));

        LatLng latLng = new LatLng(userLocation.getLatitude(), userLocation.getLongitude());
        googleMap.moveCamera(latLng);

        googleMap.addMarker(new MarkerOptions().position(latLng));
        locationTxt.setText(mRentEditHashMap.get("vLocation"));
        locationTxt.setVisibility(mRentEditHashMap.get("eIsUserAddressDisplay").equalsIgnoreCase("Yes") ? View.VISIBLE : View.GONE);
    }

    private void likeAndOwnerView() {
        RelativeLayout favouriteArea = findViewById(R.id.favouriteArea);
        LikeButton favouriteButton = findViewById(R.id.favouriteButton);
        favouriteArea.setVisibility(View.GONE);
        favouriteButton.setVisibility(View.GONE);

        LinearLayout contactOwnerArea = findViewById(R.id.contactOwnerArea);
        contactOwnerArea.setVisibility(View.GONE);

        /* boolean isFavouriteView = getIntent().getBooleanExtra("isFavouriteView", false);

        // TODO : favorite force fully | FALSE
        isFavouriteView = false;

        if (isFavouriteView) {
            favouriteArea.setVisibility(View.VISIBLE);
            favouriteButton.setVisibility(View.VISIBLE);

            favouriteButton.setLiked(Objects.requireNonNull(mRentEditHashMap.get("eIsFavourite")).equalsIgnoreCase("Yes"));

            favouriteButton.setOnLikeListener(new OnLikeListener() {
                @Override
                public void liked(LikeButton likeButton) {
                    if (SystemClock.elapsedRealtime() - mLastClickTime < 1000) {
                        return;
                    }
                    mLastClickTime = SystemClock.elapsedRealtime();
                    setFavouriteRentItem(true);
                }

                @Override
                public void unLiked(LikeButton likeButton) {
                    if (SystemClock.elapsedRealtime() - mLastClickTime < 1000) {
                        return;
                    }
                    mLastClickTime = SystemClock.elapsedRealtime();
                    setFavouriteRentItem(false);
                }

                private void setFavouriteRentItem(boolean isLike) {
                    HashMap<String, String> parameters = new HashMap<>();
                    parameters.put("type", "UpdateUserFavouriteRentPost");
                    parameters.put("iMemberId", generalFunc.getMemberId());
                    parameters.put("iRentItemPostId", mRentEditHashMap.get("iRentItemPostId"));
                    parameters.put("eIsFavourite", isLike ? "Yes" : "No");

                    ApiHandler.execute(getApplicationContext(), parameters, true, false, generalFunc, responseString -> {

                    });
                }
            });
        }*/
        if (getIntent().getBooleanExtra("isOwnerView", false)) {
            contactOwnerArea.setVisibility(View.VISIBLE);
            callBtnTxt = ((MaterialRippleLayout) findViewById(R.id.callBtnTxt)).getChildView();
            callBtnTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CALL_TXT"));
            lineArea = findViewById(R.id.lineArea);
            isInquiryDone = mRentEditHashMap.containsKey("isOwnerPostSendInquiry") && Objects.requireNonNull(mRentEditHashMap.get("isOwnerPostSendInquiry")).equalsIgnoreCase("Yes");
            sendInquiryBtnTxt = ((MaterialRippleLayout) findViewById(R.id.sendInquiryBtnTxt)).getChildView();
            callBtnTxt.setId(Utils.generateViewId());
            sendInquiryBtnTxt.setId(Utils.generateViewId());
            addToClickHandler(callBtnTxt);
            addToClickHandler(sendInquiryBtnTxt);
        }
    }

    private void setData() {
        // pickup Time Slot Data
        pickupTimeSlotFieldRow();

        txtPostPrice.setVisibility(Utils.checkText(mRentEditHashMap.get("fAmount")) ? View.VISIBLE : View.GONE);
        txtPostPrice.setText(mRentEditHashMap.get("fAmount"));

        txtPostDurationStatus.setVisibility(Utils.checkText(mRentEditHashMap.get("eRentItemDuration")) ? View.VISIBLE : View.GONE);
        txtPostDurationStatus.setText("/ " + mRentEditHashMap.get("eRentItemDuration"));

        txtPostedTxt.setText(mRentEditHashMap.get("PostedTxt"));

        // rentItem Field Data set
        rentItemFieldRow();

        // Images data set
        JSONArray arr_data = generalFunc.getJsonArray(mRentEditHashMap.get("Images"));
        mediaList.clear();
        if (arr_data != null && arr_data.length() > 0) {
            photosArea.setVisibility(View.VISIBLE);

            RelativeLayout.LayoutParams bannerLayoutParams = (RelativeLayout.LayoutParams) photosArea.getLayoutParams();
            bannerLayoutParams.width = bannerWidth;
            bannerLayoutParams.height = bannerHeight;
            photosArea.setLayoutParams(bannerLayoutParams);

            MyUtils.createArrayListJSONArray(generalFunc, mediaList, arr_data);
        } else {
            photosArea.setVisibility(View.GONE);
        }
        photoAdapter.notifyDataSetChanged();
        if (mediaList.size() > 1) {
            if (generalFunc.isRTLmode()) {
                photosViewPager.setCurrentItem(mediaList.size() - 1);
            }
            dotsIndicator.setViewPager(photosViewPager);
            dotsIndicator.setVisibility(View.VISIBLE);
            dotsArea.setVisibility(View.VISIBLE);
        } else {
            dotsArea.setVisibility(View.GONE);
        }
    }

    private void pickupTimeSlotFieldRow() {

        JSONArray fieldArray = generalFunc.getJsonArray(mRentEditHashMap.get("timeslot"));
        if (fieldArray != null) {
            if (pickupTimeSlotContainer.getChildCount() > 0) {
                pickupTimeSlotContainer.removeAllViewsInLayout();
            }
            for (int i = 0; i < fieldArray.length(); i++) {
                JSONObject jobject = generalFunc.getJsonObject(fieldArray, i);
                try {
                    String data = Objects.requireNonNull(jobject.names()).getString(0);
                    String row_value = jobject.get(data).toString();
                    pickupTimeSlotContainer.addView(addItemFieldData(generalFunc, data, row_value));

                } catch (JSONException e) {
                    Logger.e("Exception", "::" + e.getMessage());
                }
            }
            if (pickupTimeSlotContainer.getChildCount() > 0) {
                pickupAvailibilityArea.setVisibility(VISIBLE);
            } else {
                pickupAvailibilityArea.setVisibility(GONE);
            }
        }
    }

    private void rentItemFieldRow() {
        JSONArray fieldArray = generalFunc.getJsonArray(mRentEditHashMap.get("RentitemFieldArr"));
        if (fieldArray != null) {
            if (rentItemDetailsContainer.getChildCount() > 0) {
                rentItemDetailsContainer.removeAllViewsInLayout();
            }
            txtPostTitle.setVisibility(View.GONE);
            cardPostDescription.setVisibility(View.GONE);
            rentDetailsArea.setVisibility(View.GONE);
            for (int i = 0; i < fieldArray.length(); i++) {
                JSONObject mObject = generalFunc.getJsonObject(fieldArray, i);
                if (i == (fieldArray.length() - 1)) {
                    divider_gone = true;
                }
                if (mObject.has("eName") && generalFunc.getJsonValueStr("eName", mObject).equalsIgnoreCase("Yes")) {
                    txtPostTitle.setVisibility(View.VISIBLE);
                    txtPostTitle.setText(generalFunc.getJsonValueStr("vValue", mObject));
                    if (Utils.checkText(mRentEditHashMap.get("vItemName"))) {
                        txtPostTitle.setText(mRentEditHashMap.get("vItemName"));
                    }
                } else if (mObject.has("eDescription") && generalFunc.getJsonValueStr("eDescription", mObject).equalsIgnoreCase("Yes")) {
                    cardPostDescription.setVisibility(View.VISIBLE);
                    txtPostDescription.setText(generalFunc.getJsonValueStr("vValue", mObject));
                } else {
                    rentItemDetailsContainer.addView(addItemFieldData(generalFunc, generalFunc.getJsonValueStr("vTitle", mObject), generalFunc.getJsonValueStr("vValue", mObject)));
                }
            }

            if (rentItemDetailsContainer.getChildCount() > 0) {
                rentDetailsArea.setVisibility(View.VISIBLE);
            }
        }
    }

    @SuppressLint("MissingInflatedId")
    private View addItemFieldData(@NonNull GeneralFunctions generalFunc, String data, String row_value) {
        LayoutInflater infalInflater = (LayoutInflater) getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        @SuppressLint("InflateParams") View convertView = infalInflater.inflate(R.layout.item_rent_daynamic_field, null);

        convertView.setLayoutParams(new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT));

        MTextView titleHTxt = convertView.findViewById(R.id.titleHTxt);
        MTextView titleVTxt = convertView.findViewById(R.id.titleVTxt);
        View divider_view = convertView.findViewById(R.id.divider_view);
        if (divider_gone) {
            divider_view.setVisibility(GONE);
        }

        titleHTxt.setText(generalFunc.convertNumberWithRTL(data));
        titleVTxt.setText(generalFunc.convertNumberWithRTL(row_value));

        return convertView;
    }

    public void onClick(View view) {
        if (view.getId() == R.id.backImgView) {
            finish();
        } else if (view.getId() == R.id.MapBtnImgView) {
            openNavigationDialog(false);
        } else if (view.getId() == R.id.DirectionBtnImgView) {
            openNavigationDialog(true);
        } else if (view.getId() == R.id.locationTxt) {
            android.content.ClipboardManager clipboard = (android.content.ClipboardManager) this.getSystemService(Context.CLIPBOARD_SERVICE);
            android.content.ClipData clip = android.content.ClipData.newPlainText("Copied Text", Utils.getText(locationTxt));
            clipboard.setPrimaryClip(clip);
            generalFunc.showMessage(generalFunc.getCurrentView(RentItemReviewPostActivity.this), generalFunc.retrieveLangLBl("Address is copied to clipboard", "LBL_RENT_ADDRESS_COPY_CLIPBOARD"));

        } else if (view.getId() == sendInquiryBtnTxt.getId()) {
            Bundle bn = new Bundle();
            bn.putSerializable("rentEditHashMap", mRentEditHashMap);
            new ActUtils(this).startActForResult(RentItemInquiry.class, bn, MyUtils.REFRESH_DATA_REQ_CODE);
        } else if (view.getId() == callBtnTxt.getId()) {
            MediaDataProvider mDataProvider = new MediaDataProvider.Builder()
                    .setToMemberId(mRentEditHashMap.get("iUserId"))
                    .setPhoneNumber(mRentEditHashMap.get("vUserPhone"))
                    .setToMemberType(Utils.CALLTOPASSENGER)
                    .setToMemberName(mRentEditHashMap.get("vUserName"))
                    .setToMemberImage(mRentEditHashMap.get("vUserImage"))
                    .setMedia(CommunicationManager.MEDIA_TYPE)
                    .build();

            String callingMethod = generalFunc.getJsonValueStr("CALLING_METHOD_BUY_SELL_RENT", obj_userProfile);
            if (callingMethod.equalsIgnoreCase("VOIP")) {
                CommunicationManager.getInstance().communicatePhoneOrVideo(MyApp.getInstance().getCurrentAct(), mDataProvider, CommunicationManager.TYPE.PHONE_CALL);
            } else if (callingMethod.equalsIgnoreCase("VIDEOCALL")) {
                CommunicationManager.getInstance().communicatePhoneOrVideo(MyApp.getInstance().getCurrentAct(), mDataProvider, CommunicationManager.TYPE.VIDEO_CALL);
            } else if (callingMethod.equalsIgnoreCase("VOIP-VIDEOCALL")) {
                CommunicationManager.getInstance().communicatePhoneOrVideo(MyApp.getInstance().getCurrentAct(), mDataProvider, CommunicationManager.TYPE.BOTH_CALL);
            } else if (!Utils.checkText(callingMethod) || callingMethod.equalsIgnoreCase("NORMAL")) {
                DefaultCommunicationHandler.getInstance().executeAction(MyApp.getInstance().getCurrentAct(), CommunicationManager.TYPE.PHONE_CALL, mDataProvider);
            }
        }
    }

    @Override
    public void onBackPressed() {
        super.onBackPressed();
        if (isInquiryDone) {
            (new ActUtils(this)).setOkResult();
        }
    }

    @Override
    protected void onResume() {
        super.onResume();
        if (getIntent().getBooleanExtra("isOwnerView", false)) {
            /*callBtnTxt.setVisibility(mRentEditHashMap.get("eIsUserNumberDisplay").equalsIgnoreCase("Yes") ? isInquiryDone ? View.VISIBLE : View.GONE : View.GONE);
            lineArea.setVisibility(mRentEditHashMap.get("eIsUserNumberDisplay").equalsIgnoreCase("Yes") ? isInquiryDone ? View.VISIBLE : View.GONE : View.GONE);
            sendInquiryBtnTxt.setText(isInquiryDone ? generalFunc.retrieveLangLBl("", "LBL_RENT_SEND_INQUIRY_TXT") : generalFunc.retrieveLangLBl("", "LBL_RENT_CONTACT_OWNER"));*/

            callBtnTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_CONTACT_OWNER"));
            callBtnTxt.setVisibility(isInquiryDone ? View.VISIBLE : View.GONE);
            ll_call_btn.setVisibility(isInquiryDone ? View.VISIBLE : View.GONE);
            lineArea.setVisibility(View.GONE);
            sendInquiryBtnTxt.setVisibility(isInquiryDone ? GONE : VISIBLE);
            ll_send_enquiry_btn.setVisibility(isInquiryDone ? GONE : VISIBLE);
            sendInquiryBtnTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RENT_CONTACT_OWNER"));
        }
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == MyUtils.REFRESH_DATA_REQ_CODE && resultCode == RESULT_OK) {
            isInquiryDone = true;
        }
    }

    private void openNavigationDialog(boolean isDirection) {
        MyCommonDialog.navigationDialog(getActContext(), generalFunc, () -> {
            try {
                if (isDirection) {
                    Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse("google.navigation:q=" + mRentEditHashMap.get("vLatitude") + "," + mRentEditHashMap.get("vLongitude")));
                    startActivity(intent);
                } else {
                    String url_view = "http://maps.google.com/maps?q=loc:" + mRentEditHashMap.get("vLatitude") + "," + mRentEditHashMap.get("vLongitude");
                    (new ActUtils(this)).openURL(url_view, "com.google.android.apps.maps", "com.google.android.maps.MapsActivity");
                }
            } catch (Exception e) {
                generalFunc.showMessage(locationTxt, generalFunc.retrieveLangLBl("Please install Google Maps in your device.", "LBL_INSTALL_GOOGLE_MAPS"));
            }
        }, () -> {
            try {
                if (isDirection) {
                    String uri = "https://waze.com/ul?q=" + MyApp.getInstance().currentLocation.getLatitude() + "," + MyApp.getInstance().currentLocation.getLongitude() + "&ll=" + mRentEditHashMap.get("vLatitude") + "," + mRentEditHashMap.get("vLongitude") + "&navigate=yes";
                    Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(uri));
                    startActivity(intent);
                } else {
                    String uri = "https://waze.com/ul?ll=" + mRentEditHashMap.get("vLatitude") + "," + mRentEditHashMap.get("vLongitude");
                    Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(uri));
                    startActivity(intent);
                }
            } catch (Exception e) {
                generalFunc.showMessage(locationTxt, generalFunc.retrieveLangLBl("Please install Waze navigation app in your device.", "LBL_INSTALL_WAZE"));
            }
        });
    }
}