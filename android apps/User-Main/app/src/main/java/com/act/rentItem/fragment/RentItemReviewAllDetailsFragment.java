package com.act.rentItem.fragment;

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
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.RecyclerView;

import com.act.rentItem.RentItemNewPostActivity;
import com.act.rentItem.adapter.RentGalleryImagesAdapter;
import com.act.rentItem.model.RentItemData;
import com.dialogs.MyCommonDialog;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.map.GeoMapLoader;
import com.map.models.LatLng;
import com.map.models.MarkerOptions;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MTextView;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class RentItemReviewAllDetailsFragment extends Fragment implements GeoMapLoader.OnMapReadyCallback, RentGalleryImagesAdapter.OnItemClickListener {

    @Nullable
    private RentItemNewPostActivity mActivity;
    private GeoMapLoader.GeoMap geoMap;
    private MTextView txtPostTitle, txtPostPrice, txtPostDurationStatus, txtPostDescription, locationTxt, CategoryHTxt, selectedcategoryHTxt, txtPlanName, itemName;
    private LinearLayout pickupTimeSlotContainer, rentDetailsArea, rentItemDetailsContainer, rentSelectedPlanArea;
    private RelativeLayout photosArea;
    private final ArrayList<HashMap<String, String>> mediaList = new ArrayList<>();
    private RecyclerView rvRentPostImages;
    private RentGalleryImagesAdapter mAdapter;

    private Location userLocation;
    private LinearLayout cardPostDescription, pickupAvailibilityArea;


    @SuppressLint("MissingInflatedId")
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {

        View view = inflater.inflate(R.layout.fragment_rent_item_review_all_details, container, false);
        assert mActivity != null;

        txtPostTitle = view.findViewById(R.id.txtPostTitle);
        txtPostPrice = view.findViewById(R.id.txtPostPrice);
        txtPostDurationStatus = view.findViewById(R.id.txtPostDurationStatus);
        pickupAvailibilityArea = view.findViewById(R.id.pickupAvailibilityArea);

        cardPostDescription = view.findViewById(R.id.cardPostDescription);
        MTextView postDescriptionHTxt = view.findViewById(R.id.postDescriptionHTxt);
        postDescriptionHTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_DESCRIPTION_TXT"));
        txtPostDescription = view.findViewById(R.id.txtPostDescription);

        CategoryHTxt = view.findViewById(R.id.CategoryHTxt);
        txtPlanName = view.findViewById(R.id.txtPlanName);
        itemName = view.findViewById(R.id.itemName);
        selectedcategoryHTxt = view.findViewById(R.id.selectedcategoryHTxt);

        ImageView MapBtnImgView = view.findViewById(R.id.MapBtnImgView);
        ImageView DirectionBtnImgView = view.findViewById(R.id.DirectionBtnImgView);
        MapBtnImgView.setOnClickListener(view1 -> openNavigationDialog(false));
        DirectionBtnImgView.setOnClickListener(view12 -> openNavigationDialog(true));

        MTextView pickUpAvailabilityHTxt = view.findViewById(R.id.pickUpAvailabilityHTxt);
        if (mActivity.eType.equalsIgnoreCase("RentEstate") || mActivity.eType.equalsIgnoreCase("RentCars")) {
            pickUpAvailabilityHTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_ESTATE_AVAILABILTY_TXT"));
        } else {
            pickUpAvailabilityHTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_ITEM_PICKUP_AVAILBILITY"));
        }
        pickupTimeSlotContainer = view.findViewById(R.id.pickupTimeSlotContainer);

        MTextView detailsHTxt = view.findViewById(R.id.detailsHTxt);
        if (mActivity.eType.equalsIgnoreCase("RentEstate")) {
            detailsHTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_PROPERTY_DETAIL"));
            itemName.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_PROPERTY_NAME"));
        } else if (mActivity.eType.equalsIgnoreCase("RentCars")) {
            detailsHTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_CAR_DETAILS"));
            itemName.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_CAR_NAME"));
        } else {
            detailsHTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_ITEM_DETAILS"));
            itemName.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_ITEM_NAME"));
        }
        rentDetailsArea = view.findViewById(R.id.rentDetailsArea);
        rentItemDetailsContainer = view.findViewById(R.id.rentItemDetailsContainer);

        MTextView photosHTxt = view.findViewById(R.id.photosHTxt);
        photosHTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_ITEM_PHOTOS"));
        photosArea = view.findViewById(R.id.photosArea);
        photosArea.setVisibility(GONE);

        MTextView locationHTxt = view.findViewById(R.id.locationHTxt);
        locationHTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_LOCATION_DETAILS"));
        locationTxt = view.findViewById(R.id.locationTxt);

        rentSelectedPlanArea = view.findViewById(R.id.rentSelectedPlanArea);
        MTextView planHTxt = view.findViewById(R.id.planHTxt);
        planHTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_SELECTED_PAYMENT_PLAN"));

        rvRentPostImages = view.findViewById(R.id.rvRentPostImages_review);

        return view;
    }

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (MyApp.getInstance().getCurrentAct() instanceof RentItemNewPostActivity) {
            mActivity = (RentItemNewPostActivity) requireActivity();

            mAdapter = new RentGalleryImagesAdapter(mActivity, mediaList, mActivity.generalFunc, false, this);
        }
    }

    @Override
    public void onResume() {
        super.onResume();
        if (mActivity != null) {
            if (mActivity.eType.equalsIgnoreCase("RentEstate")) {
                mActivity.selectServiceTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_ESTATE_PROPERTY_REVIEW_DETAILS"));
            } else if (mActivity.eType.equalsIgnoreCase("RentCars")) {
                mActivity.selectServiceTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_CAR_REVIEWS_DETAILS"));
            } else {
                mActivity.selectServiceTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_ITEM_REVIEW_DETAILS"));
            }
            (new GeoMapLoader(mActivity, R.id.mapRentContainer)).bindMap(this);
        }
    }

    @SuppressLint("PotentialBehaviorOverride")
    @Override
    public void onMapReady(@NonNull GeoMapLoader.GeoMap googleMap) {
        assert mActivity != null;
        this.geoMap = googleMap;
        googleMap.getUiSettings().setAllGesturesEnabled(false);
        if (mActivity.generalFunc.checkLocationPermission(true)) {
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

        RentItemData.LocationDetails locationDetails = mActivity.mRentItemData.getLocationDetails();
        if (locationDetails != null) {
            userLocation = new Location("source");
            userLocation.setLatitude(GeneralFunctions.parseDoubleValue(0.0, locationDetails.getvLatitude()));
            userLocation.setLongitude(GeneralFunctions.parseDoubleValue(0.0, locationDetails.getvLongitude()));

            //locationTxt.setText(locationDetails.getvLocation());
            locationTxt.setText(locationDetails.getvLocation());

            LatLng latLng = new LatLng(userLocation.getLatitude(), userLocation.getLongitude());
            googleMap.moveCamera(latLng);
            googleMap.addMarker(new MarkerOptions().position(latLng));
        }
    }

    @SuppressLint("SetTextI18n")
    public void setData(@NonNull GeneralFunctions generalFunc, @NonNull String responseString) {
        assert mActivity != null;
        // pickup Time Slot Data
        pickupTimeSlotFieldRow(generalFunc, responseString);

        txtPostPrice.setVisibility(Utils.checkText(generalFunc.getJsonValue("fAmount", responseString)) ? VISIBLE : GONE);
        txtPostPrice.setText(generalFunc.getJsonValue("fAmount", responseString));

        txtPostDurationStatus.setVisibility(Utils.checkText(generalFunc.getJsonValue("eRentItemDuration", responseString)) ? VISIBLE : GONE);
        txtPostDurationStatus.setText("/ " + generalFunc.getJsonValue("eRentItemDuration", responseString));

        selectedcategoryHTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_SELECTED_CATEGORY"));
        if (Utils.checkText(generalFunc.getJsonValue("vSubCatName", responseString))) {
            CategoryHTxt.setText(generalFunc.getJsonValue("vCatName", responseString) + " (" + generalFunc.getJsonValue("vSubCatName", responseString) + ")");
        } else {
            CategoryHTxt.setText(generalFunc.getJsonValue("vCatName", responseString));
        }

        // rentItem Field Data set
        rentItemFieldRow(generalFunc, responseString);

        // Images data set
        JSONArray arr_data = generalFunc.getJsonArray("Images", responseString);
        mediaList.clear();
        if (arr_data != null && arr_data.length() > 0) {
            photosArea.setVisibility(VISIBLE);

            for (int i = 0; i < arr_data.length(); i++) {
                JSONObject obj_tmp = generalFunc.getJsonObject(arr_data, i);
                HashMap<String, String> mapData = new HashMap<>();
                MyUtils.createHashMap(generalFunc, mapData, obj_tmp);
                mapData.put("isDelete", "No");
                mediaList.add(mapData);
            }
            rvRentPostImages.setAdapter(mAdapter);
        } else {
            photosArea.setVisibility(GONE);
        }

        // Location data set
        userLocation = new Location("source");
        userLocation.setLatitude(GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValue("vLatitude", responseString)));
        userLocation.setLongitude(GeneralFunctions.parseDoubleValue(0.0, generalFunc.getJsonValue("vLongitude", responseString)));
        if (geoMap != null) {
            LatLng latLng = new LatLng(userLocation.getLatitude(), userLocation.getLongitude());
            geoMap.moveCamera(latLng);
            geoMap.addMarker(new MarkerOptions().position(latLng));
        }
        //locationTxt.setText(generalFunc.getJsonValue("vLocation", responseString));
        locationTxt.setText(generalFunc.getJsonValue("vAddress", responseString));
        locationTxt.setOnClickListener(view -> {
            android.content.ClipboardManager clipboard = (android.content.ClipboardManager) mActivity.getSystemService(Context.CLIPBOARD_SERVICE);
            android.content.ClipData clip = android.content.ClipData.newPlainText("Copied Text", Utils.getText(locationTxt));
            clipboard.setPrimaryClip(clip);
            generalFunc.showMessage(generalFunc.getCurrentView(mActivity), generalFunc.retrieveLangLBl("Address is copied to clipboard", "LBL_RENT_ADDRESS_COPY_CLIPBOARD"));
        });
        if (mActivity != null) {
            mActivity.setPagerHeight();
        }

        // selected Plan
        rentSelectedPlanArea.setVisibility(GONE);
        if (mActivity != null && mActivity.mRentEditHashMap != null) {
            rentSelectedPlanArea.setVisibility(VISIBLE);

            JSONObject planData = generalFunc.getJsonObject(mActivity.mRentEditHashMap.get("RentItemPlanData"));
            txtPlanName.setText(generalFunc.getJsonValueStr("vPlanName", planData));

        }
    }

    private void pickupTimeSlotFieldRow(@NonNull GeneralFunctions generalFunc, @NonNull String responseString) {

        JSONArray fieldArray = generalFunc.getJsonArray("timeslot", responseString);
        if (fieldArray != null) {
            if (pickupTimeSlotContainer.getChildCount() > 0) {
                pickupTimeSlotContainer.removeAllViewsInLayout();
            }
            for (int i = 0; i < fieldArray.length(); i++) {
                JSONObject jobject = generalFunc.getJsonObject(fieldArray, i);
                try {
                    String data = Objects.requireNonNull(jobject.names()).getString(0);
                    String row_value = jobject.get(data).toString();
                    pickupTimeSlotContainer.addView(addItemFieldData(generalFunc, data, row_value, i == (fieldArray.length() - 1)));

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

    private void rentItemFieldRow(@NonNull GeneralFunctions generalFunc, @NonNull String responseString) {

        JSONArray fieldArray = generalFunc.getJsonArray("RentitemFieldArr", responseString);
        if (fieldArray != null) {
            if (rentItemDetailsContainer.getChildCount() > 0) {
                rentItemDetailsContainer.removeAllViewsInLayout();
            }
            txtPostTitle.setVisibility(GONE);
            cardPostDescription.setVisibility(GONE);
            rentDetailsArea.setVisibility(GONE);
            for (int i = 0; i < fieldArray.length(); i++) {
                JSONObject mObject = generalFunc.getJsonObject(fieldArray, i);
                if (mObject.has("eName") && generalFunc.getJsonValueStr("eName", mObject).equalsIgnoreCase("Yes")) {
                    txtPostTitle.setVisibility(VISIBLE);
                    txtPostTitle.setText(generalFunc.getJsonValueStr("vValue", mObject));
                    if (Utils.checkText(generalFunc.getJsonValueStr("vItemName", mObject))) {
                        txtPostTitle.setText(generalFunc.getJsonValueStr("vItemName", mObject));
                    }
                } else if (mObject.has("eDescription") && generalFunc.getJsonValueStr("eDescription", mObject).equalsIgnoreCase("Yes")) {
                    cardPostDescription.setVisibility(VISIBLE);
                    txtPostDescription.setText(generalFunc.getJsonValueStr("vValue", mObject));
                } else {
                    rentItemDetailsContainer.addView(addItemFieldData(generalFunc, generalFunc.getJsonValueStr("vTitle", mObject), generalFunc.getJsonValueStr("vValue", mObject), i == (fieldArray.length() - 1)));
                }
            }
            if (rentItemDetailsContainer.getChildCount() > 0) {
                rentDetailsArea.setVisibility(VISIBLE);
            }
        }
    }

    private View addItemFieldData(@NonNull GeneralFunctions generalFunc, String data, String row_value, boolean divider_view_gone) {
        LayoutInflater infalInflater = (LayoutInflater) requireActivity().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        @SuppressLint("InflateParams") View convertView = infalInflater.inflate(R.layout.item_rent_daynamic_field, null);

        convertView.setLayoutParams(new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT));

        MTextView titleHTxt = convertView.findViewById(R.id.titleHTxt);
        MTextView titleVTxt = convertView.findViewById(R.id.titleVTxt);
        View divider_view = convertView.findViewById(R.id.divider_view);
        if (divider_view_gone) {
            divider_view.setVisibility(GONE);
        }

        titleHTxt.setText(generalFunc.convertNumberWithRTL(data));
        titleVTxt.setText(generalFunc.convertNumberWithRTL(row_value));

        return convertView;
    }

    public void checkPageNext() {
        if (mActivity != null) {
            mActivity.setPageNext();
        }
    }

    private void openNavigationDialog(boolean isDirection) {
        assert mActivity != null;
        MyCommonDialog.navigationDialog(mActivity, mActivity.generalFunc,
                () -> {
                    try {
                        if (isDirection) {
                            Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse("google.navigation:q=" + userLocation.getLatitude() + "," + userLocation.getLongitude()));
                            startActivity(intent);
                        } else {
                            String url_view = "http://maps.google.com/maps?q=loc:" + userLocation.getLatitude() + "," + userLocation.getLongitude();
                            (new ActUtils(mActivity)).openURL(url_view, "com.google.android.apps.maps", "com.google.android.maps.MapsActivity");
                        }
                    } catch (Exception e) {
                        mActivity.generalFunc.showMessage(txtPostDescription, mActivity.generalFunc.retrieveLangLBl("Please install Google Maps in your device.", "LBL_INSTALL_GOOGLE_MAPS"));
                    }
                }, () -> {
                    try {
                        if (isDirection) {
                            String uri = "https://waze.com/ul?q=" + MyApp.getInstance().currentLocation.getLatitude() + "," + MyApp.getInstance().currentLocation.getLongitude() + "&ll=" + userLocation.getLatitude() + "," + userLocation.getLatitude() + "&navigate=yes";
                            Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(uri));
                            startActivity(intent);
                        } else {
                            String uri = "https://waze.com/ul?ll=" + userLocation.getLatitude() + "," + userLocation.getLongitude();
                            Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(uri));
                            startActivity(intent);
                        }

                    } catch (Exception e) {
                        mActivity.generalFunc.showMessage(txtPostDescription, mActivity.generalFunc.retrieveLangLBl("Please install Waze navigation app in your device.", "LBL_INSTALL_WAZE"));
                    }
                });
    }

    @Override
    public void onItemClickList(View v, int position) {
        //(new ActUtils(getContext())).openURL(mediaList.get(position).get("vImage"));
        mActivity.showImage(mediaList.get(position).get("vImage"));
    }

    @Override
    public void onDeleteClick(View v, int position) {

    }
}