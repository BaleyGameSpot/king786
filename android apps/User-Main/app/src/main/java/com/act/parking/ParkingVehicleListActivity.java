package com.act.parking;

import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

import androidx.annotation.NonNull;
import androidx.appcompat.widget.Toolbar;
import androidx.databinding.DataBindingUtil;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.SpacesItemDecoration;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityParkingVehicleListBinding;
import com.act.parking.adapter.VehicleListAdapter;
import com.service.handler.ApiHandler;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MTextView;

import org.json.JSONArray;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class ParkingVehicleListActivity extends ParentActivity {
    private ActivityParkingVehicleListBinding binding;
    public Toolbar mToolbar;
    private VehicleListAdapter adapter;
    boolean isDelete = false;
    boolean mIsLoading = false, isNextPageAvailable = false;
    private ArrayList<HashMap<String, String>> vehicleList = new ArrayList<>();
    private String vehicleSizeId = "";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_parking_vehicle_list);

        mToolbar = findViewById(R.id.toolbar);
        setSupportActionBar(mToolbar);
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);
        if (getIntent().hasExtra("iParkingVehicleSizeId")) {
            vehicleSizeId = getIntent().getStringExtra("iParkingVehicleSizeId");

        }
        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_PARKING_VEHICLE_LIST_TXT"));
        adapter = new VehicleListAdapter(this, generalFunc, vehicleList, new VehicleListAdapter.OnClickListener() {
            @Override
            public void onItemClick(int position, HashMap<String, String> mapData) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_PARKING_SELECT_VEHICLE_CONFIRM_MSG"), generalFunc.retrieveLangLBl("", "LBL_NO"), generalFunc.retrieveLangLBl("", "LBL_YES"), button_Id -> {
                    if (button_Id == 1) {
                        selectVehicle(mapData);
                    }
                });
            }

            @Override
            public void onDeleteClick(int position, HashMap<String, String> mapData) {
                generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_DELETE_CAR_SURE"), generalFunc.retrieveLangLBl("", "LBL_NO"), generalFunc.retrieveLangLBl("", "LBL_YES"), button_Id -> {
                    if (button_Id == 1) {
                        deleteVehicle(mapData);
                    }
                });

            }

            @Override
            public void onEditClick(int position, HashMap<String, String> mapData) {
                Bundle bn = new Bundle();
                bn.putString("detailType", "car");
                bn.putSerializable("vehicleData", mapData);
                bn.putSerializable("vehicleSizes", getIntent().getSerializableExtra("vehicleSizes"));
                new ActUtils(ParkingVehicleListActivity.this).startActWithData(AddParkingCarOrDriverActivity.class, bn);
            }
        }, false);

        binding.rvVehicles.addItemDecoration(new SpacesItemDecoration(1, getResources().getDimensionPixelSize(R.dimen._12sdp), false));
        binding.rvVehicles.setAdapter(adapter);
        binding.rvVehicles.addOnScrollListener(new RecyclerView.OnScrollListener() {
            @Override
            public void onScrolled(@NonNull RecyclerView recyclerView, int dx, int dy) {
                super.onScrolled(recyclerView, dx, dy);
                if (recyclerView.canScrollVertically(1)) {
                    int visibleItemCount = Objects.requireNonNull(binding.rvVehicles.getLayoutManager()).getChildCount();
                    int totalItemCount = binding.rvVehicles.getLayoutManager().getItemCount();
                    int firstVisibleItemPosition = ((LinearLayoutManager) binding.rvVehicles.getLayoutManager()).findFirstVisibleItemPosition();

                    int lastInScreen = firstVisibleItemPosition + visibleItemCount;
                    Logger.d("SIZEOFLIST", "::" + lastInScreen + "::" + totalItemCount + "::" + isNextPageAvailable);
                    if (((lastInScreen == totalItemCount) && !(mIsLoading) && isNextPageAvailable)) {
                        mIsLoading = true;
//                        binding.footerLoader.setVisibility(View.VISIBLE);
                        binding.rvVehicles.stopScroll();
                        getVehicleList();

                    } else if (!isNextPageAvailable) {
//                        binding.footerLoader.setVisibility(View.GONE);
                    }
                }
            }
        });

        addToClickHandler(binding.addVehicle);
        getVehicleList();

    }

    public void getVehicleList() {
        binding.mProgressBar.setVisibility(View.VISIBLE);
        binding.rvVehicles.setVisibility(View.GONE);
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "GetUserVehicleForParkingSpace");
        parameters.put("UserType", Utils.app_type);
        parameters.put("iParkingVehicleSizeId", getIntent().getStringExtra("iParkingVehicleSizeId"));
        if (Utils.checkText(getIntent().getStringExtra("vehicleId"))) {
            parameters.put("iParkingVehicleId", getIntent().getStringExtra("vehicleId"));
        }
        parameters.put("iUserId", generalFunc.getMemberId());
        ApiHandler.execute(this, parameters, false, false, generalFunc, responseString -> {
            binding.mProgressBar.setVisibility(View.GONE);
            if (responseString != null && !responseString.equalsIgnoreCase("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    binding.rvVehicles.setVisibility(View.VISIBLE);
                    vehicleList.clear();
                    JSONArray vehiclesArray = generalFunc.getJsonArray(Utils.message_str, responseString);
                    MyUtils.createArrayListJSONArray(generalFunc, vehicleList, vehiclesArray);
                    adapter.notifyDataSetChanged();
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private void deleteVehicle(HashMap<String, String> mapData) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "DeleteUserVehicleForParkingSpace");
        parameters.put("UserType", Utils.app_type);
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("iParkingVehicleId", mapData.get("iParkingVehicleId"));
        if (Utils.checkText(getIntent().getStringExtra("vehicleId"))) {
            if (getIntent().getStringExtra("vehicleId").equalsIgnoreCase(mapData.get("iParkingVehicleId"))) {
                isDelete = true;
            }
        }

        ApiHandler.execute(this, parameters, true, false, generalFunc, responseString -> {
            if (responseString != null && !responseString.equalsIgnoreCase("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    getVehicleList();
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private void selectVehicle(HashMap<String, String> mapData) {
        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "SelectUserVehicleForParkingSpace");
        parameters.put("iParkingVehicleSizeId", vehicleSizeId);
        parameters.put("iParkingVehicleId", mapData.get("iParkingVehicleId"));
        ApiHandler.execute(this, parameters, true, false, generalFunc, responseString -> {
            if (responseString != null && !responseString.equalsIgnoreCase("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {
                    Bundle bn = new Bundle();
                    bn.putSerializable("vehicleData", mapData);
                    bn.putString("detailType", "car");
                    new ActUtils(ParkingVehicleListActivity.this).setOkResult(bn);
                    finish();
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }


    public void onClick(View view) {
        int i = view.getId();
        if (i == R.id.backImgView) {
            Bundle bn = new Bundle();
            bn.putSerializable("vehicleList", vehicleList);
            bn.putSerializable("detailType", "car");
            new ActUtils(ParkingVehicleListActivity.this).setOkResult(bn);
            finish();
        } else if (i == binding.addVehicle.getId()) {
            Bundle bn = new Bundle();
            bn.putString("detailType", "car");
            bn.putSerializable("vehicleSizes", getIntent().getSerializableExtra("vehicleSizes"));
            new ActUtils(ParkingVehicleListActivity.this).startActWithData(AddParkingCarOrDriverActivity.class, bn);
        }
    }

    @Override
    public void onBackPressed() {
        Bundle bn = new Bundle();
        bn.putSerializable("vehicleList", vehicleList);
        bn.putSerializable("detailType", "car");
        new ActUtils(ParkingVehicleListActivity.this).setOkResult(bn);
        super.onBackPressed();
    }

    @Override
    public void onResume() {
        super.onResume();
        getVehicleList();
    }
}