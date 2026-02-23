package com.act.nearbyservice;

import android.annotation.SuppressLint;
import android.content.Context;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;
import android.widget.ScrollView;

import androidx.cardview.widget.CardView;
import androidx.core.content.ContextCompat;
import androidx.databinding.DataBindingUtil;

import com.ViewPagerCards.HomeCardPagerAdapter;
import com.ViewPagerCards.ShadowTransformer;
import com.act.MainActivity;
import com.act.deliverAll.BuyAnythingActivity;
import com.act.deliverAll.RestaurantAllDetailsNewActivity;
import com.act.nearbyservice.adapter.ServiceActionAdapter;
import com.activity.ParentActivity;
import com.dialogs.BottomInfoDialog;
import com.general.call.CommunicationManager;
import com.general.call.MediaDataProvider;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityNearByDetailsBinding;
import com.service.handler.ApiHandler;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class NearByDetailsActivity extends ParentActivity {

    private ActivityNearByDetailsBinding binding;
    private HashMap<String, String> mPlacesHashMap;

    private ServiceActionAdapter mServiceActionAdapter;
    public ArrayList<HashMap<String, String>> serviceActionList = new ArrayList<>();

    //
    private LinearLayout openingHrArea;
    private MTextView titleDailogTxt, addressDailogTxt;
    private CardView informationDesignCardView, ratingDesignCardView;

    private MButton closeBtn;
    private final ArrayList<HashMap<String, String>> timeSlotsArray = new ArrayList<>();
    private String LBL_CLOSED_TXT;
    private MTextView timeHTxt, timeSatTxt, timeVTxt, timeVSatTxt;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_near_by_details);

        mPlacesHashMap = (HashMap<String, String>) getIntent().getSerializableExtra("placesHashMap");
        if (mPlacesHashMap == null) {
            return;
        }

        ImageView backImgView = findViewById(R.id.backImgView);
        addToClickHandler(backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }

        manageVectorImage(binding.viewGradient, R.drawable.ic_gradient_gray, R.drawable.ic_gradient_gray_compat);

        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NEARBY_DETAILS"));

        timeHTxt = findViewById(R.id.timeHTxt);
        timeSatTxt = findViewById(R.id.timeSatTxt);
        timeVTxt = findViewById(R.id.timeVTxt);
        timeVSatTxt = findViewById(R.id.timeVSatTxt);

        LinearLayout.LayoutParams lyParamsBannerArea = (LinearLayout.LayoutParams) binding.imagesArea.getLayoutParams();
        lyParamsBannerArea.height = Utils.getHeightOfBanner(getActContext(), 0, "16:9");
        binding.imagesArea.setLayoutParams(lyParamsBannerArea);

        RelativeLayout.LayoutParams lyParamsBannerArea1 = (RelativeLayout.LayoutParams) binding.bannerArea.getLayoutParams();
        lyParamsBannerArea1.height = Utils.getHeightOfBanner(getActContext(), 0, "16:9");
        binding.bannerArea.setLayoutParams(lyParamsBannerArea1);

        timeShow();

        BottomInfoDialog bottomInfoDialog = new BottomInfoDialog(getActContext(), generalFunc);

        //////////////////////
        mServiceActionAdapter = new ServiceActionAdapter(generalFunc, serviceActionList, (mapData, drawable) -> {

            Bundle bn = new Bundle();
            switch (Objects.requireNonNull(mapData.get("eType"))) {
                case "Call" -> {
                    MediaDataProvider mDataProvider1 = new MediaDataProvider.Builder()
                            .setPhoneNumber(mapData.get("vPhone"))
                            .setToMemberName(mapData.get("vPhone"))
                            .setMedia(CommunicationManager.MEDIA.DEFAULT).build();
                    CommunicationManager.getInstance().communicate(getActContext(), mDataProvider1, CommunicationManager.TYPE.OTHER);
                }
                case "Taxi" -> {
                    bn.putString("latitude", getIntent().getStringExtra("vLatitude"));
                    bn.putString("longitude", getIntent().getStringExtra("vLongitude"));
                    bn.putString("address", getIntent().getStringExtra("vLocation"));
                    bn.putString("vPlacesLocation", mapData.get("vPlacesLocation"));
                    bn.putString("vPlacesLocationLat", mapData.get("vPlacesLocationLat"));
                    bn.putString("vPlacesLocationLong", mapData.get("vPlacesLocationLong"));
                    bn.putBoolean("isPlacesLocation", true);
                    new ActUtils(getActContext()).startActWithData(MainActivity.class, bn);
                }
                case "Genie" -> {
                    bn.putString("vServiceAddress", mapData.get("vPlacesLocation"));
                    bn.putString("vStoreName", mapData.get("vStoreName"));
                    bn.putString("latitude", mapData.get("vPlacesLocationLat"));
                    bn.putString("longitude", mapData.get("vPlacesLocationLong"));
                    bn.putBoolean("isStoreAdded", true);
                    bn.putString("vCategory", generalFunc.retrieveLangLBl("", "LBL_OTHER_DELIVERY"));
                    bn.putString("eSelectStore", "Yes");
                    new ActUtils(getActContext()).startActWithData(BuyAnythingActivity.class, bn);
                }
                case "DeliveryAll" -> {
                    JSONObject object = generalFunc.getJsonObject(mapData.get("CompanyDetails"));
                    bn.putString("iCompanyId", generalFunc.getJsonValueStr("iCompanyId", object));
                    bn.putString("Restaurant_Status", generalFunc.getJsonValueStr("restaurantstatus", object));
                    bn.putString("Restaurant_Safety_Status", generalFunc.getJsonValueStr("Restaurant_Safety_Status", object));
                    bn.putString("Restaurant_Safety_Icon", generalFunc.getJsonValueStr("Restaurant_Safety_Icon", object));
                    bn.putString("Restaurant_Safety_URL", generalFunc.getJsonValueStr("Restaurant_Safety_URL", object));
                    bn.putString("lat", getIntent().getStringExtra("vLatitude"));
                    bn.putString("long", getIntent().getStringExtra("vLongitude"));
                    bn.putString("ispriceshow", generalFunc.getJsonValueStr("ispriceshow", object));
                    bn.putString("eAvailable", generalFunc.getJsonValueStr("eAvailable", object));
                    bn.putString("timeslotavailable", generalFunc.getJsonValueStr("timeslotavailable", object));
                    generalFunc.storeData(Utils.iServiceId_KEY, generalFunc.getJsonValueStr("iServiceId", object));
                    new ActUtils(getActContext()).startActForResult(RestaurantAllDetailsNewActivity.class, bn, 111);
                }
                case "Location" -> {
                    try {
                        String url_view = "http://maps.google.com/maps?daddr=" + mapData.get("vPlacesLocationLat") + "," + mapData.get("vPlacesLocationLong");
                        (new ActUtils(getActContext())).openURL(url_view, "com.google.android.apps.maps", "com.google.android.maps.MapsActivity");
                    } catch (Exception e) {
                        generalFunc.showMessage(binding.bannerArea, generalFunc.retrieveLangLBl("Please install Google Maps in your device.", "LBL_INSTALL_GOOGLE_MAPS"));
                    }
                }
                case "DiscountOffer" ->
                        bottomInfoDialog.showPreferenceDialog(mapData.get("vTitle"), mapData.get("vOfferDiscount"), drawable, generalFunc.retrieveLangLBl("", "LBL_OK"));
            }

        });
        binding.rvNearByServiceAction.setAdapter(mServiceActionAdapter);
        /////////////////////

        getNearByPlacesList();
    }

    private Context getActContext() {
        return NearByDetailsActivity.this;
    }

    @SuppressLint("NotifyDataSetChanged")
    private void getNearByPlacesList() {

        binding.loadingImages.setVisibility(View.VISIBLE);
        binding.mainArea.setVisibility(View.GONE);

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getNearByPlaces");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("iCategoryId", mPlacesHashMap.get("iNearByCategoryId"));

        parameters.put("vLatitude", "" + getIntent().getStringExtra("vLatitude"));
        parameters.put("vLongitude", "" + getIntent().getStringExtra("vLongitude"));

        parameters.put("iNearByPlacesId", mPlacesHashMap.get("iNearByPlacesId"));

        ApiHandler.execute(getActContext(), parameters, responseString -> {
            JSONObject responseObj = generalFunc.getJsonObject(responseString);

            binding.loadingImages.setVisibility(View.GONE);

            if (responseObj != null && !responseObj.toString().equals("")) {

                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseObj)) {

                    binding.mainArea.setVisibility(View.VISIBLE);

                    JSONArray placesArr = generalFunc.getJsonArray("Places", responseString);
                    JSONObject objData = generalFunc.getJsonObject(placesArr, 0);

                    setImages(objData);
                    dataSet(objData);
                    timeSet(objData);

                    JSONArray serviceActionArr = generalFunc.getJsonArray("ServiceAction", objData);
                    serviceActionList.clear();
                    if (serviceActionArr != null && serviceActionArr.length() > 0) {
                        MyUtils.createArrayListJSONArray(generalFunc, serviceActionList, serviceActionArr);
                    }
                    mServiceActionAdapter.notifyDataSetChanged();

                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseObj)));
                }

            } else {
                generalFunc.showError();
            }
        });
    }

    private void setImages(JSONObject objData) {

        JSONArray imagesArr = generalFunc.getJsonArray("vImages", objData);

        if (imagesArr != null && imagesArr.length() > 0) {
            ArrayList<String> imagesList = new ArrayList<>();
            HomeCardPagerAdapter mCardAdapter = new HomeCardPagerAdapter();

            int bannerWidth = (int) (Utils.getScreenPixelWidth(getActContext()));
            int bannerHeight = (int) (bannerWidth / 1.77);

            LinearLayout.LayoutParams lyParamsBannerArea = (LinearLayout.LayoutParams) binding.imagesArea.getLayoutParams();
            lyParamsBannerArea.height = Utils.getHeightOfBanner(getActContext(), 0, "16:9");
            binding.imagesArea.setLayoutParams(lyParamsBannerArea);

            RelativeLayout.LayoutParams lyParamsBannerArea1 = (RelativeLayout.LayoutParams) binding.bannerArea.getLayoutParams();
            lyParamsBannerArea1.height = Utils.getHeightOfBanner(getActContext(), 0, "16:9");
            binding.bannerArea.setLayoutParams(lyParamsBannerArea1);

            if (generalFunc.isRTLmode()) {
                for (int x = imagesArr.length() - 1; x >= 0; x--) {
                    String vImage = (String) generalFunc.getValueFromJsonArr(imagesArr, x);
                    String imageURL = Utils.getResizeImgURL(MyApp.getInstance().getCurrentAct(), vImage, bannerWidth, bannerHeight);

                    imagesList.add(imageURL);
                    mCardAdapter.addCardItem(imageURL, getActContext(), bannerHeight);
                }
            } else {
                for (int i = 0; i < imagesArr.length(); i++) {
                    String vImage = (String) generalFunc.getValueFromJsonArr(imagesArr, i);
                    String imageURL = Utils.getResizeImgURL(MyApp.getInstance().getCurrentAct(), vImage, bannerWidth, bannerHeight);

                    imagesList.add(imageURL);
                    mCardAdapter.addCardItem(imageURL, getActContext(), bannerHeight);
                }
            }

            ShadowTransformer mCardShadowTransformer = new ShadowTransformer(binding.bannerViewPager, mCardAdapter);

            binding.bannerViewPager.setAdapter(mCardAdapter);
            binding.bannerViewPager.setPageTransformer(false, mCardShadowTransformer);
            binding.bannerViewPager.setOffscreenPageLimit(3);
            if (imagesArr.length() > 1) {
                if (generalFunc.isRTLmode()) {
                    binding.bannerViewPager.setCurrentItem(imagesList.size() - 1);
                }
                binding.dotsIndicator.setViewPager(binding.bannerViewPager);
                binding.dotsIndicator.setVisibility(View.VISIBLE);
                binding.dotsArea.setVisibility(View.VISIBLE);
            } else {
                binding.dotsArea.setVisibility(View.GONE);
            }
        } else {
            binding.imagesArea.setVisibility(View.GONE);
        }
    }

    private void dataSet(JSONObject objData) {
        binding.restaurantNameTxt.setText(generalFunc.getJsonValueStr("vTitle", objData));
        binding.placesLocationTXT.setText(generalFunc.getJsonValueStr("vAddress", objData));
        binding.categoryNameTxt.setText(generalFunc.getJsonValueStr("vCategoryName", objData));
        binding.deliveryTimeTxt.setText(generalFunc.getJsonValueStr("duration", objData));

        binding.statusMessageTxt.setText(generalFunc.getJsonValueStr("statusMessage", objData));
        if (Utils.checkText(generalFunc.getJsonValueStr("placesStatus", objData)) && generalFunc.getJsonValueStr("placesStatus", objData).equalsIgnoreCase("Open")) {
            binding.statusMessageTxt.setTextColor(ContextCompat.getColor(getActContext(), R.color.Green));
        } else {
            binding.statusMessageTxt.setTextColor(ContextCompat.getColor(getActContext(), R.color.Red));
        }

        String openCloseTimeMessage = generalFunc.getJsonValueStr("openCloseTimeMessage", objData);
        if (Utils.checkText(openCloseTimeMessage)) {
            binding.txtDash.setVisibility(View.VISIBLE);
            binding.openCloseTimeMessageTxt.setText(openCloseTimeMessage);
        } else {
            binding.txtDash.setVisibility(View.GONE);
        }

        if (Utils.checkText(generalFunc.getJsonValueStr("vAboutPlaces", objData))) {
            binding.aboutPlaceLayout.setVisibility(View.VISIBLE);
            binding.aboutPlaceHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NEARBY_ABOUT_PLACE"));
            binding.aboutPlacesTXT.setText(generalFunc.getJsonValueStr("vAboutPlaces", objData));
        } else {
            binding.aboutPlaceLayout.setVisibility(View.GONE);
        }

    }

    private void timeShow() {
        addToClickHandler(binding.timeArea);

        informationDesignCardView = findViewById(R.id.informationDesignCardView);
        informationDesignCardView.setVisibility(View.GONE);
        ratingDesignCardView = findViewById(R.id.ratingDesignCardView);

        titleDailogTxt = findViewById(R.id.titleDailogTxt);
        addressDailogTxt = findViewById(R.id.addressDailogTxt);
        MTextView openingHourTxt = findViewById(R.id.openingHourTxt);
        openingHourTxt.setText(generalFunc.retrieveLangLBl("", "LBL_OPENING_HOURS"));

        LinearLayout oldTimeSlotsArea = findViewById(R.id.oldTimeSlotsArea);
        ScrollView newTimeSlotsArea = findViewById(R.id.newTimeSlotsArea);
        if (generalFunc.retrieveValue("ENABLE_TIMESLOT_ADDON").equalsIgnoreCase("Yes")) {
            oldTimeSlotsArea.setVisibility(View.GONE);
            newTimeSlotsArea.setVisibility(View.VISIBLE);
        } else {
            oldTimeSlotsArea.setVisibility(View.VISIBLE);
            newTimeSlotsArea.setVisibility(View.GONE);
        }
        openingHrArea = findViewById(R.id.openingHrArea);

        LBL_CLOSED_TXT = generalFunc.retrieveLangLBl("", "LBL_CLOSED_TXT");

        closeBtn = ((MaterialRippleLayout) findViewById(R.id.closeBtn)).getChildView();
        addToClickHandler(closeBtn);
        closeBtn.setText(generalFunc.retrieveLangLBl("", "LBL_CLOSE_TXT"));
    }

    @SuppressLint("InflateParams")
    private void timeSet(JSONObject objData) {

        titleDailogTxt.setText(generalFunc.getJsonValueStr("vTitle", objData));
        addressDailogTxt.setText(generalFunc.getJsonValueStr("vAddress", objData));

        if (generalFunc.retrieveValue("ENABLE_TIMESLOT_ADDON").equalsIgnoreCase("Yes")) {

            timeSlotsArray.clear();
            if (openingHrArea.getChildCount() > 0) {
                openingHrArea.removeAllViewsInLayout();
            }

            setOpeningHrData(generalFunc.getJsonValueStr("vMonToSlot1", objData), generalFunc.retrieveLangLBl("", "LBL_MONDAY_TXT"));
            setOpeningHrData(generalFunc.getJsonValueStr("vTueToSlot1", objData), generalFunc.retrieveLangLBl("", "LBL_TUESDAY_TXT"));
            setOpeningHrData(generalFunc.getJsonValueStr("vWedToSlot1", objData), generalFunc.retrieveLangLBl("", "LBL_WEDNESDAY_TXT"));
            setOpeningHrData(generalFunc.getJsonValueStr("vThuToSlot1", objData), generalFunc.retrieveLangLBl("", "LBL_THURSDAY_TXT"));
            setOpeningHrData(generalFunc.getJsonValueStr("vFriToSlot1", objData), generalFunc.retrieveLangLBl("", "LBL_FRIDAY_TXT"));
            setOpeningHrData(generalFunc.getJsonValueStr("vSatToSlot1", objData), generalFunc.retrieveLangLBl("", "LBL_SATURDAY_TXT"));
            setOpeningHrData(generalFunc.getJsonValueStr("vSunToSlot1", objData), generalFunc.retrieveLangLBl("", "LBL_SUNDAY_TXT"));

            MyUtils.timeSlotRow(getActContext(), generalFunc, openingHrArea, timeSlotsArray, true);
        } else {
            timeHTxt.setText(generalFunc.getJsonValueStr("monfritimeslot_TXT", objData));
            timeSatTxt.setText(generalFunc.getJsonValueStr("satsuntimeslot_TXT", objData));
            timeVTxt.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("monfritimeslot_Time", objData)));
            timeVSatTxt.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("satsuntimeslot_Time", objData)));
        }
    }

    private void setOpeningHrData(String slotTime, String dayName) {
        HashMap<String, String> setOpeningHrData = new HashMap<>();
        setOpeningHrData.put("DayName", dayName);
        setOpeningHrData.put("DayTime", Utils.checkText(slotTime) ? slotTime : LBL_CLOSED_TXT);
        timeSlotsArray.add(setOpeningHrData);
    }

    @Override
    public void onBackPressed() {
        if (binding.dialogsLayout.getVisibility() == View.VISIBLE) {
            closeBtn.performClick();
        } else {
            super.onBackPressed();
        }
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        if (!generalFunc.getMemberId().equalsIgnoreCase("")) {
            generalFunc.storeData(Utils.iServiceId_KEY, "");
        }
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            onBackPressed();
        } else if (i == R.id.timeArea) {
            if (binding.dialogsLayout.getVisibility() == View.GONE) {
                binding.dialogsLayout.setVisibility(View.VISIBLE);
                binding.dialogsLayoutArea.setVisibility(View.VISIBLE);
                informationDesignCardView.setVisibility(View.VISIBLE);
                ratingDesignCardView.setVisibility(View.GONE);
            } else {
                binding.dialogsLayout.setVisibility(View.GONE);
                binding.dialogsLayoutArea.setVisibility(View.GONE);
            }
        } else if (i == closeBtn.getId()) {
            if (binding.dialogsLayout.getVisibility() == View.VISIBLE) {
                binding.dialogsLayout.setVisibility(View.GONE);
                binding.dialogsLayoutArea.setVisibility(View.GONE);
            }
        }
    }
}