package com.act.rideSharingPro.fragment;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;

import com.act.rideSharingPro.RideSharingProHomeActivity;
import com.act.rideSharingPro.RideSharingUtils;
import com.act.rideSharingPro.RideUploadDocActivity;
import com.act.rideSharingPro.adapter.RideDocumentAdapter;
import com.act.rideSharingPro.model.RideProPublishData;
import com.fragments.BaseFragment;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.FragmentRidePublishStep4Binding;
import com.model.ServiceModule;
import com.service.handler.ApiHandler;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class RidePublishStep4Fragment extends BaseFragment {

    private FragmentRidePublishStep4Binding binding;
    @Nullable
    private RideSharingProHomeActivity mActivity;
    private RideDocumentAdapter rideDocumentAdapter;
    public ArrayList<HashMap<String, String>> verificationDocumentList = new ArrayList<>();
    private boolean isDone = true;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {

        binding = DataBindingUtil.inflate(inflater, R.layout.fragment_ride_publish_step_4, container, false);

        binding.rvVerificationDocument.setAdapter(rideDocumentAdapter);

        binding.MainArea.setVisibility(View.GONE);
        binding.loading.setVisibility(View.GONE);

        return binding.getRoot();
    }

    @SuppressLint("NotifyDataSetChanged")
    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (MyApp.getInstance().getCurrentAct() instanceof RideSharingProHomeActivity) {
            mActivity = (RideSharingProHomeActivity) requireActivity();

            rideDocumentAdapter = new RideDocumentAdapter(verificationDocumentList, new RideDocumentAdapter.OnItemClickListener() {
                @Override
                public void onItemClickList(HashMap<String, String> mapData) {
                    Intent intent = new Intent(mActivity, RideUploadDocActivity.class);
                    Bundle bn = new Bundle();
                    bn.putBoolean("isOnlyShow", false);
                    bn.putSerializable("documentDataHashMap", mapData);
                    intent.putExtras(bn);
                    launchActivity.launch(intent);
                    isDone = false;
                }

                @Override
                public void onUpdateDocumentIds(String documentIds) {
                    mActivity.rsPublishFragment.mPublishData.setDocumentIds(documentIds);
                }
            });
        }
    }

    ActivityResultLauncher<Intent> launchActivity = registerForActivityResult(new ActivityResultContracts.StartActivityForResult(), result -> {
        if (mActivity != null && result.getResultCode() == Activity.RESULT_OK) {
            isDone = true;
        }
    });

    @Override
    public void onResume() {
        super.onResume();
        if (mActivity != null) {
            binding.selectServiceTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_REVIEW_PUBLISH_DETAILS_TXT"));
            mActivity.rsPublishFragment.binding.headerHTxt.setText(Utils.getText(binding.selectServiceTxt));
            if (isDone) {
                getVerificationDoc(mActivity.generalFunc);
            }
        }
    }

    @SuppressLint("SetTextI18n")
    private void getVerificationDoc(GeneralFunctions generalFunc) {
        assert mActivity != null;

        ArrayList<RideProPublishData.MultiStopData> multiStopData = mActivity.rsPublishFragment.mPublishData.getMultiStopData();
        if (multiStopData == null) {
            return;
        }

        binding.MainArea.setVisibility(View.GONE);
        binding.loading.setVisibility(View.VISIBLE);

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "GetVerificationDocuments");
        parameters.put("iMemberId", generalFunc.getMemberId());

        parameters.put("tStartLat", "" + multiStopData.get(0).getDestLat());
        parameters.put("tStartLong", "" + multiStopData.get(0).getDestLong());

        parameters.put("tEndLat", "" + multiStopData.get(multiStopData.size() - 1).getDestLat());
        parameters.put("tEndLong", "" + multiStopData.get(multiStopData.size() - 1).getDestLong());

        parameters.put("dStartDate", mActivity.rsPublishFragment.mPublishData.getDateTime());
        parameters.put("fPrice", mActivity.rsPublishFragment.mPublishData.getRecommendedPrice());

        parameters.put("tSAddress", "" + multiStopData.get(0).getDestAddress());
        parameters.put("tDAddress", "" + multiStopData.get(multiStopData.size() - 1).getDestAddress());

        parameters.put("PointRecommendedPrice", mActivity.rsPublishFragment.mPublishData.getPointRecommendedPrice());

        ApiHandler.execute(requireActivity(), parameters, (String responseString) -> {

            binding.loading.setVisibility(View.GONE);
            binding.MainArea.setVisibility(View.VISIBLE);

            if (responseString != null && !responseString.equalsIgnoreCase("")) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {

                    binding.verifyDocHTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_VERIFY_DOCS_TITLE"));
                    binding.ridePlanHTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_RIDE_PLAN_TITLE"));
                    //binding.ridePlanVTxt.setText(generalFunc.getJsonValueStr("StartDate", generalFunc.getJsonObject(responseString)));
                    binding.ridePlanVTxt.setText(generalFunc.getJsonValueStr("tDisplayDate", generalFunc.getJsonObject(responseString)));

                    binding.startTimeTxt.setText(generalFunc.getJsonValueStr("StartTime", generalFunc.getJsonObject(responseString)));
                    binding.endTimeTxt.setText(generalFunc.getJsonValueStr("EndTime", generalFunc.getJsonObject(responseString)));

                    binding.sLocTagTxt.setText(generalFunc.getJsonValueStr("SourceLocationPoint", generalFunc.getJsonObject(responseString)));
                    binding.eLocTagTxt.setText(generalFunc.getJsonValueStr("DestLocationPoint", generalFunc.getJsonObject(responseString)));

                    binding.startCityTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_DETAILS_START_LOC_TXT"));
                    binding.endCityTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_DETAILS_END_LOC_TXT"));

                    mActivity.rsPublishFragment.mPublishData.setStartCity(generalFunc.getJsonValueStr("StartCity", generalFunc.getJsonObject(responseString)));
                    mActivity.rsPublishFragment.mPublishData.setEndCity(generalFunc.getJsonValueStr("EndCity", generalFunc.getJsonObject(responseString)));

                    binding.priceInfoArea.setVisibility(View.GONE);

                    binding.priceInfoTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_PRICE_DETAILS"));
                    binding.numberOfPassengersHtxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RIDESHARE_TOTAL_SEATS"));
                    binding.pricePerSeatHTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_PRICE_PER_SEAT_TOTAL_TXT"));
                    binding.numberOfPassengerstxt.setText(mActivity.rsPublishFragment.mPublishData.getPerSeat());
                    binding.pricePerSeatTxt.setText(generalFunc.getJsonValue("CurrencySymbol", mActivity.obj_userProfile) + " " + mActivity.rsPublishFragment.mPublishData.getRecommendedPrice());

                    binding.startAddressTxt.setText(generalFunc.getJsonValueStr("StartAddress", generalFunc.getJsonObject(responseString)));
                    binding.endAddressTxt.setText(generalFunc.getJsonValueStr("EndAddress", generalFunc.getJsonObject(responseString)));

                    dynamicView(responseString);

                    JSONArray messageArr = generalFunc.getJsonArray(Utils.message_str, responseString);
                    verificationDocumentList.clear();
                    if (messageArr != null && messageArr.length() > 0) {
                        binding.docArea.setVisibility(View.VISIBLE);
                        binding.selectServiceTxt.setVisibility(View.VISIBLE);
                        MyUtils.createArrayListJSONArray(generalFunc, verificationDocumentList, messageArr);
                    } else {
                        binding.docArea.setVisibility(View.GONE);
                        binding.selectServiceTxt.setVisibility(View.GONE);
                    }
                    rideDocumentAdapter.updateData();
                    mActivity.rsPublishFragment.setPagerHeight();
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private void dynamicView(String responseString) {
        assert mActivity != null;
        if (binding.dynamicStopPointView.getChildCount() > 0) {
            binding.dynamicStopPointView.removeAllViewsInLayout();
        }

        JSONArray waypointsArr = mActivity.generalFunc.getJsonArray("waypoints", responseString);
        if (waypointsArr != null) {
            RideSharingUtils.wayPointsView(mActivity, mActivity.generalFunc, waypointsArr, binding.dynamicStopPointView);
        }

        // multiStop Fare
        if (binding.multiStopFareDataView.getChildCount() > 0) {
            binding.multiStopFareDataView.removeAllViewsInLayout();
        }
        JSONArray waypointFareArr = mActivity.generalFunc.getJsonArray("waypointFare", responseString);
        if (waypointFareArr != null && ServiceModule.EnableRideSharingPro) {
            binding.multiStopFareHTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_STOP_OVER_POINT_PRICE_RIDE_SHARE_TEXT"));
            for (int i = 0; i < waypointFareArr.length(); i++) {
                JSONObject jobject = mActivity.generalFunc.getJsonObject(waypointFareArr, i);
                try {
                    String data = Objects.requireNonNull(jobject.names()).getString(0);

                    RideSharingUtils.addSummaryRow(mActivity, mActivity.generalFunc, binding.multiStopFareDataView, data, jobject.get(data).toString(), false);
                } catch (JSONException e) {
                    Logger.e("Exception", "::" + e.getMessage());
                }
            }
        }
        binding.multiStopFareArea.setVisibility(binding.multiStopFareDataView.getChildCount() > 0 ? View.VISIBLE : View.GONE);
    }

    public void checkPageNext() {
        if (mActivity != null) {
            if (verificationDocumentList != null && !verificationDocumentList.isEmpty()) {
                if (mActivity.rsPublishFragment.mPublishData.getDocumentIds() != null) {
                    if (Utils.checkText(mActivity.rsPublishFragment.mPublishData.getDocumentIds())) {
                        String[] separated = mActivity.rsPublishFragment.mPublishData.getDocumentIds().split(",");
                        if (verificationDocumentList.size() == separated.length) {
                            mActivity.rsPublishFragment.sendToPublishRide();
                        } else {
                            mActivity.generalFunc.showMessage(binding.selectServiceTxt, mActivity.generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_UPLOAD_ALL_DOCUMENT"));
                        }
                    } else {
                        mActivity.generalFunc.showMessage(binding.selectServiceTxt, mActivity.generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_UPLOAD_ALL_DOCUMENT"));
                    }
                }
            } else {
                mActivity.rsPublishFragment.sendToPublishRide();
            }

        }
    }
}