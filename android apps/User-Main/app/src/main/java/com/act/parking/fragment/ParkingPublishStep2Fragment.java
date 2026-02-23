package com.act.parking.fragment;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;

import com.fragments.BaseFragment;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.FragmentParkingPublishStep2Binding;
import com.act.parking.ParkingPublish;
import com.act.parking.adapter.ParkingVehicleSizeAdapter;
import com.service.handler.ApiHandler;
import com.utils.Utils;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class ParkingPublishStep2Fragment extends BaseFragment {

    FragmentParkingPublishStep2Binding binding;
    ParkingPublish mActivity;
    private GeneralFunctions generalFunc;

    ParkingVehicleSizeAdapter adapter;
    private int DEFAULT_SPACE_COUNT = 1;
    private ArrayList<HashMap<String, String>> list;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = DataBindingUtil.inflate(inflater, R.layout.fragment_parking_publish_step_2, container, false);
        setlabels();
        return binding.getRoot();
    }

    @Override
    public void onResume() {
        if (list.size() == 0) {
            getVehicleSizes();
        }
        super.onResume();
        if (mActivity != null) {
            binding.parkingAddress.setText(mActivity.parkingPublishLocationData.getParkingAddress());
        }
    }

    private void getVehicleSizes() {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "GetParkingVehicleSize");
        parameters.put("UserType", Utils.app_type);
        parameters.put("iUserId", generalFunc.getMemberId());

        ApiHandler.execute(mActivity, parameters, true, false, generalFunc, responseString -> {
            if (responseString != null && !responseString.equalsIgnoreCase("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    list.clear();
                    JSONArray arr_data = generalFunc.getJsonArray(Utils.message_str, responseString);
                    if (arr_data != null) {
                        for (int i = 0; i < arr_data.length(); i++) {
                            JSONObject obj_tmp = generalFunc.getJsonObject(arr_data, i);
                            HashMap<String, String> mapdata = new HashMap<>();
                            mapdata.put("size", generalFunc.getJsonValueStr("tTitle", obj_tmp));
                            mapdata.put("sizeEg", generalFunc.getJsonValueStr("tSubtitle", obj_tmp));
                            mapdata.put("iParkingVehicleSizeId", generalFunc.getJsonValueStr("iParkingVehicleSizeId", obj_tmp));
                            list.add(mapdata);

                        }
                        adapter.notifyDataSetChanged();
                        mActivity.setPagerHeight();
                    }

                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }

        });
    }


    private void setlabels() {
        binding.parkingAddress.setText(mActivity.parkingPublishLocationData.getParkingAddress());
        binding.parkingLocationTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_SPACE_LOCATION_TXT"));
        binding.AddressHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_SPACE_ADDRESS_TXT"));
        binding.parkingSpaceHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_SPACE_TXT"));
        binding.vehicleSizeHtxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_VEHICLE_SIZE_TXT"));
        binding.EtAddress.setHint(generalFunc.retrieveLangLBl("", "LBL_PARKING_ENTER_ADDRESS_HINT_TXT"));
        binding.noOfParkingSpaces.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_SPACE_AVAILABLE_NO"));

        binding.parkingSpaceCount.setText(String.valueOf(DEFAULT_SPACE_COUNT));
        addToClickHandler(binding.addParkingspace);
        addToClickHandler(binding.subtractParkingSpace);
        list = new ArrayList<>();

        adapter = new ParkingVehicleSizeAdapter(mActivity, generalFunc, list, (position, selPlanPos, list) -> {
            adapter.setSelectedSeat(position);
            mActivity.parkingPublishData.setVehicleSize(list.get("iParkingVehicleSizeId"));
            adapter.notifyDataSetChanged();
//            mActivity.parkingPublishData.setVehicleSize(list.get(position));
        });
        binding.RvVehicleSize.setAdapter(adapter);
    }

    public void onClickView(View view) {
        Utils.hideKeyboard(getActivity());
        int i = view.getId();
        if (i == binding.addParkingspace.getId()) {
            DEFAULT_SPACE_COUNT = DEFAULT_SPACE_COUNT + 1;
            binding.parkingSpaceCount.setText("" + DEFAULT_SPACE_COUNT);
        } else if (i == binding.subtractParkingSpace.getId() && DEFAULT_SPACE_COUNT > 1) {
            DEFAULT_SPACE_COUNT = DEFAULT_SPACE_COUNT - 1;
            binding.parkingSpaceCount.setText("" + DEFAULT_SPACE_COUNT);
        }

    }


    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (MyApp.getInstance().getCurrentAct() instanceof ParkingPublish) {
            mActivity = (ParkingPublish) requireActivity();
            generalFunc = mActivity.generalFunc;
        }
    }

    public void checkPageNext() {
        if (mActivity != null) {

            mActivity.parkingPublishData.setParkingSpaceNo(binding.parkingSpaceCount.getText().toString());

            if (Utils.checkText(binding.EtAddress) && Utils.checkText(binding.parkingAddress.getText().toString()) && Utils.checkText(mActivity.parkingPublishData.getVehicleSize())) {
                mActivity.parkingPublishData.setFullAddress(binding.EtAddress.getText().toString());
                mActivity.setPageNext();
            } else {
                if (!Utils.checkText(binding.EtAddress)) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_PARKING_ENTER_ADDRESS_VALIDATION_TXT"), buttonId -> {

                    });
                } else if (!Utils.checkText(mActivity.parkingPublishData.getVehicleSize())) {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_PARKING_SELECT_VEHICLE_SIZE_TXT"), buttonId -> {
                    });
                }
            }
        }
    }


}
