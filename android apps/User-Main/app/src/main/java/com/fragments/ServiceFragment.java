package com.fragments;

import android.content.Context;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;

import com.act.MoreInfoActivity;
import com.act.UberxCartActivity;
import com.adapter.files.CategoryListItem;
import com.adapter.files.PinnedCategorySectionListAdapter;
import com.dialogs.OpenListView;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.FragmentServicesBinding;
import com.realmModel.CarWashCartData;
import com.service.handler.ApiHandler;
import com.utils.Logger;
import com.utils.Utils;
import com.view.GenerateAlertBox;

import org.json.JSONArray;
import org.json.JSONObject;

import java.io.Serializable;
import java.util.ArrayList;
import java.util.HashMap;

import io.realm.Realm;
import io.realm.RealmResults;

public class ServiceFragment extends BaseFragment implements PinnedCategorySectionListAdapter.ServiceClick {

    private FragmentServicesBinding binding;
    private GeneralFunctions generalFunc;
    private MoreInfoActivity moreInfoAct;
    private int hourCnt = 0, regCnt = 0, fixCnt = 0;

    private PinnedCategorySectionListAdapter pinnedSectionListAdapter;
    public ArrayList<CategoryListItem> allCategoryItemsList = new ArrayList<>();
    public String eVideoConsultServiceCharge = "";

    //
    private final ArrayList<HashMap<String, String>> vehicleSizeHashMap = new ArrayList<>();
    private String mSelectedSizeId;
    private int selVehicleSizePos = -1;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {

        if (binding != null) {
            return binding.getRoot();
        }
        binding = DataBindingUtil.inflate(inflater, R.layout.fragment_services, container, false);

        moreInfoAct = (MoreInfoActivity) requireActivity();
        generalFunc = moreInfoAct.generalFunc;
        binding.noResTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NO_DATA_AVAIL"));

        binding.serviceList.setShadowVisible(true);
        binding.serviceList.setFastScrollEnabled(false);
        binding.serviceList.setFastScrollAlwaysVisible(false);

        binding.vehicleSizeArea.setVisibility(View.GONE);
        addToClickHandler(binding.vehicleSizeArea);

        getServiceInfo(false);

        return binding.getRoot();
    }

    @Override
    public void onResume() {
        super.onResume();
        if (!allCategoryItemsList.isEmpty() && pinnedSectionListAdapter != null) {
            for (int i = 0; i < allCategoryItemsList.size(); i++) {
                CategoryListItem tmpItem = allCategoryItemsList.get(i);
                if (tmpItem.type == CategoryListItem.ITEM) {
                    tmpItem.setAdd(checkSameRecordExist(MyApp.getRealmInstance(), tmpItem.getiVehicleTypeId()));
                }
            }
            pinnedSectionListAdapter.notifyDataSetChanged();
        }
    }

    private Context getActContext() {
        return moreInfoAct.getActContext();
    }

    private void getServiceInfo(boolean isVehicleSizeChange) {
        if (!isVehicleSizeChange) {
            binding.loadingBar.setVisibility(View.VISIBLE);
        }
        binding.noResTxt.setVisibility(View.GONE);

        final HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getDriverServiceCategories");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("iDriverId", moreInfoAct.getIntent().getStringExtra("iDriverId"));
        parameters.put("SelectedCabType", Utils.CabGeneralType_UberX);
        parameters.put("parentId", moreInfoAct.getIntent().getStringExtra("parentId"));
        parameters.put("SelectedVehicleTypeId", moreInfoAct.getIntent().getStringExtra("SelectedVehicleTypeId"));
        parameters.put("vSelectedLatitude", moreInfoAct.getIntent().getStringExtra("latitude"));
        parameters.put("vSelectedLongitude", moreInfoAct.getIntent().getStringExtra("longitude"));
        parameters.put("vSelectedAddress", moreInfoAct.getIntent().getStringExtra("address"));
        parameters.put("eForVideoConsultation", moreInfoAct.isVideoConsultEnable ? "Yes" : "No");

        if (Utils.checkText(mSelectedSizeId)) {
            parameters.put("iVehicleSizeId", mSelectedSizeId);
        }

        ApiHandler.execute(getActContext(), parameters, isVehicleSizeChange, false, generalFunc, responseString -> {

            binding.loadingBar.setVisibility(View.GONE);
            binding.noResTxt.setVisibility(View.GONE);

            JSONObject responseObj = generalFunc.getJsonObject(responseString);
            if (responseObj != null) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseObj)) {

                    allCategoryItemsList.clear();

                    JSONArray mainListArr = generalFunc.getJsonArray(Utils.message_str, responseObj);

                    int sectionPosition = 0, listPosition = 0;

                    CategoryListItem[] sections = new CategoryListItem[mainListArr.length()];

                    Realm realm = MyApp.getRealmInstance();

                    for (int i = 0; i < mainListArr.length(); i++) {
                        JSONObject tempJson = generalFunc.getJsonObject(mainListArr, i);

                        String vCategory = generalFunc.getJsonValueStr("vCategory", tempJson);

                        CategoryListItem section = new CategoryListItem(CategoryListItem.SECTION, vCategory);
                        section.sectionPosition = sectionPosition;
                        section.listPosition = listPosition++;
                        section.CountSubItems = GeneralFunctions.parseIntegerValue(0, vCategory);

                        sections[sectionPosition] = section;

                        allCategoryItemsList.add(section);

                        JSONArray subListArr = generalFunc.getJsonArray("SubCategories", tempJson);

                        if (subListArr != null) {
                            for (int j = 0; j < subListArr.length(); j++) {
                                JSONObject subTempJson = generalFunc.getJsonObject(subListArr, j);

                                CategoryListItem categoryListItem = new CategoryListItem(CategoryListItem.ITEM, generalFunc.getJsonValueStr("vCategory", tempJson));
                                categoryListItem.sectionPosition = sectionPosition;
                                categoryListItem.listPosition = listPosition++;
                                categoryListItem.setvTitle(generalFunc.getJsonValueStr("vVehicleType", subTempJson));
                                categoryListItem.setiVehicleCategoryId(generalFunc.getJsonValueStr("iVehicleCategoryId", subTempJson));
                                categoryListItem.setvDesc(generalFunc.getJsonValueStr("vCategoryDesc", subTempJson));
                                categoryListItem.setvShortDesc(generalFunc.getJsonValueStr("vCategoryShortDesc", subTempJson));
                                categoryListItem.seteFareType(generalFunc.getJsonValueStr("eFareType", subTempJson));
                                categoryListItem.setfFixedFare(generalFunc.getJsonValueStr("fFixedFare", subTempJson));
                                categoryListItem.setfPricePerHour(generalFunc.getJsonValueStr("fPricePerHour", subTempJson));
                                categoryListItem.setfMinHour(generalFunc.getJsonValueStr("fMinHour", subTempJson));
                                categoryListItem.setiVehicleTypeId(generalFunc.getJsonValueStr("iVehicleTypeId", subTempJson));
                                categoryListItem.setVideoConsultEnable(moreInfoAct.isVideoConsultEnable);
                                categoryListItem.setAdd(checkSameRecordExist(realm, generalFunc.getJsonValueStr("iVehicleTypeId", subTempJson)));
                                allCategoryItemsList.add(categoryListItem);
                                if (moreInfoAct.isVideoConsultEnable) {
                                    eVideoConsultServiceCharge = generalFunc.getJsonValueStr("eVideoConsultServiceCharge", tempJson);
                                }
                            }
                        }
                        sectionPosition++;
                    }

                    if (pinnedSectionListAdapter == null) {
                        pinnedSectionListAdapter = new PinnedCategorySectionListAdapter(getActContext(), generalFunc, allCategoryItemsList, sections);
                        binding.serviceList.setAdapter(pinnedSectionListAdapter);
                        pinnedSectionListAdapter.setserviceClickListener(this);
                    } else {
                        pinnedSectionListAdapter.notifyDataSetChanged();
                    }

                    if (!moreInfoAct.isFinishing()) {
                        moreInfoAct.onResumeCall();
                    }
                    vehicleSize(responseObj);
                }
                if (1 >= allCategoryItemsList.size()) {
                    binding.noResTxt.setVisibility(View.VISIBLE);
                }
            } else {
                generalFunc.showError(true);
            }
        });
    }

    private void vehicleSize(@NonNull JSONObject responseObj) {
        vehicleSizeHashMap.clear();
        JSONArray vehicleSizeArr = generalFunc.getJsonArray("VehicleSizeData", responseObj);
        if (vehicleSizeArr != null && vehicleSizeArr.length() > 0) {
            for (int k = 0; k < vehicleSizeArr.length(); k++) {
                JSONObject tempJson = generalFunc.getJsonObject(vehicleSizeArr, k);
                String iVehicleSizeId = generalFunc.getJsonValueStr("iVehicleSizeId", tempJson);
                String VehicleSize = generalFunc.getJsonValueStr("VehicleSize", tempJson);
                String isSelected = generalFunc.getJsonValueStr("isSelected", tempJson);

                if (isSelected.equalsIgnoreCase("Yes")) {
                    selVehicleSizePos = k;
                    mSelectedSizeId = iVehicleSizeId;
                    binding.vehicleSizeTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SERVICE_SELECTED_CAR_SIZE_TXT") + " " + VehicleSize);
                }
                vehicleSizeHashMap.add(new Gson().fromJson(tempJson.toString(), new TypeToken<HashMap<String, String>>() {
                }.getType()));
            }
            if (generalFunc.getJsonValueStr("isCarSizeSelected", responseObj).equalsIgnoreCase("Yes")) {
                if (Utils.checkText(mSelectedSizeId)) {
                    binding.vehicleSizeArea.setVisibility(View.VISIBLE);
                }
            } else {
                binding.vehicleSizeArea.performClick();
            }
        }
    }

    public void onClickView(View view) {
        Utils.hideKeyboard(getActivity());
        if (view.getId() == binding.vehicleSizeArea.getId()) {
            OpenListView.getInstance(getActContext(), generalFunc.retrieveLangLBl("", "LBL_SERVICE_SELECT_CAR_SIZE_TXT"), vehicleSizeHashMap,
                    binding.vehicleSizeArea.getVisibility() == View.GONE ? OpenListView.OpenDirection.CENTER : OpenListView.OpenDirection.BOTTOM, false, position -> {
                        selVehicleSizePos = position;

                        HashMap<String, String> mapData = vehicleSizeHashMap.get(position);
                        mSelectedSizeId = mapData.get("iVehicleSizeId");
                        binding.vehicleSizeTxt.setText(generalFunc.retrieveLangLBl("", "LBL_SERVICE_SELECTED_CAR_SIZE_TXT") + " " + mapData.get("VehicleSize"));
                        getServiceInfo(true);
                    }, binding.vehicleSizeArea.getVisibility() == View.GONE).show(selVehicleSizePos, "VehicleSize");
        }
    }

    private RealmResults<CarWashCartData> getHourData() {
        try {
            hourCnt = 0;
            regCnt = 0;
            fixCnt = 0;
            Realm realm = MyApp.getRealmInstance();
            RealmResults<CarWashCartData> data = realm.where(CarWashCartData.class).findAll();

            for (int i = 0; i < data.size(); i++) {
                CategoryListItem categoryListItem = data.get(i).getCategoryListItem();
                if (categoryListItem.geteFareType().equals("Hourly")) {
                    hourCnt = hourCnt + 1;
                } else if (categoryListItem.geteFareType().equals("Regular")) {
                    regCnt = regCnt + 1;
                } else {
                    fixCnt = fixCnt + 1;
                }
            }
        } catch (Exception e) {
            Logger.d("RealmException", "::" + e.toString());
        }

        return null;
    }


    private CarWashCartData checkSameRecordExist(Realm realm, CategoryListItem serviceListItem) {
        RealmResults<CarWashCartData> cartlist = realm.where(CarWashCartData.class).findAll();
        if (cartlist != null && !cartlist.isEmpty())
            for (int i = 0; i < cartlist.size(); i++) {
                if (serviceListItem.getiVehicleTypeId().equalsIgnoreCase(cartlist.get(i).getCategoryListItem().getiVehicleTypeId())) {
                    return cartlist.get(i);
                }
            }
        return null;
    }

    private boolean checkSameRecordExist(Realm realm, String iVehicleTypeId) {
        RealmResults<CarWashCartData> cartlist = realm.where(CarWashCartData.class).findAll();
        if (cartlist != null && !cartlist.isEmpty())
            for (int i = 0; i < cartlist.size(); i++) {
                if (iVehicleTypeId.equalsIgnoreCase(cartlist.get(i).getCategoryListItem().getiVehicleTypeId())) {
                    return true;
                }
            }
        return false;
    }

    @Override
    public void serviceClickList(CategoryListItem serviceListItem) {
        getHourData();
        Realm realm = MyApp.getRealmInstance();

        CarWashCartData carWashCartData = checkSameRecordExist(realm, serviceListItem);

        if ((serviceListItem.geteFareType().equals("Hourly") && fixCnt >= 1) || (serviceListItem.geteFareType().equals("Regular") && fixCnt >= 1)) {
            generalFunc.showMessage(moreInfoAct.searchImgView, generalFunc.retrieveLangLBl("", "LBL_RESTRICT_FIXED_SERVICE"));

        } else if ((serviceListItem.geteFareType().equals("Hourly") && hourCnt > 1) || (serviceListItem.geteFareType().equals("Fixed") && hourCnt == 1)
                || ((serviceListItem.geteFareType().equals("Hourly") && hourCnt >= 1 && carWashCartData == null)) || (serviceListItem.geteFareType().equals("Regular") && hourCnt >= 1)) {
            generalFunc.showMessage(moreInfoAct.searchImgView, generalFunc.retrieveLangLBl("", "LBL_RESTRICT_HOURLY_SERVICE"));

        } else if ((serviceListItem.geteFareType().equals("Regular") && regCnt > 1) || (serviceListItem.geteFareType().equals("Fixed") && regCnt >= 1) || (serviceListItem.geteFareType().equals("Hourly") && regCnt >= 1) || (serviceListItem.geteFareType().equals("Regular") && regCnt >= 1 && carWashCartData == null)) {
            generalFunc.showMessage(moreInfoAct.searchImgView, generalFunc.retrieveLangLBl("", "LBL_RESTRICT_REGULAR_SERVICE"));

        } else {
            Bundle bn = new Bundle();
            bn.putSerializable("data", (Serializable) serviceListItem);
            bn.putString("iDriverId", moreInfoAct.getIntent().getStringExtra("iDriverId"));
            bn.putBoolean("isVideoConsultEnable", moreInfoAct.isVideoConsultEnable);
            new ActUtils(getActContext()).startActWithData(UberxCartActivity.class, bn);
        }
    }

    @Override
    public void serviceRemoveClickList(CategoryListItem serviceListItem) {
        final GenerateAlertBox generateAlert = new GenerateAlertBox(getContext());
        generateAlert.setCancelable(false);

        generateAlert.setBtnClickList(btn_id -> {
            if (btn_id == 0) {
                generateAlert.closeAlertBox();
            } else {
                Realm realm = MyApp.getRealmInstance();
                realm.beginTransaction();
                CarWashCartData carWashCartData = checkSameRecordExist(realm, serviceListItem);
                if (carWashCartData != null) {
                    carWashCartData.deleteFromRealm();
                }
                realm.commitTransaction();
                onResume();

                moreInfoAct.onResumeCall();
            }
        });

        generateAlert.setContentMessage("", generalFunc.retrieveLangLBl("", "LBL_REMOVE_SERVICE_NOTE"));
        generateAlert.setPositiveBtn(generalFunc.retrieveLangLBl("", "LBL_YES"));
        generateAlert.setNegativeBtn(generalFunc.retrieveLangLBl("", "LBL_NO"));
        generateAlert.showAlertBox();
    }
}