package com.act.rentItem.fragment;

import android.annotation.SuppressLint;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;
import androidx.fragment.app.Fragment;

import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.FragmentRentItemPaymentPlanDetailsBinding;
import com.act.rentItem.RentItemNewPostActivity;
import com.act.rentItem.adapter.RentPaymentPlanAdapter;
import com.service.handler.ApiHandler;
import com.utils.Utils;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;

public class RentItemPaymentPlanFragment extends Fragment {

    private FragmentRentItemPaymentPlanDetailsBinding binding;
    @Nullable
    private RentItemNewPostActivity mActivity;
    private RentPaymentPlanAdapter mPlanAdapter;
    private final ArrayList<HashMap<String, String>> paymentPlanList = new ArrayList<>();
    private boolean isClick = false;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = DataBindingUtil.inflate(inflater, R.layout.fragment_rent_item_payment_plan_details, container, false);

        binding.rvRentPaymentPlan.setAdapter(mPlanAdapter);
        if (mActivity != null) {
            getPlan(mActivity.generalFunc);
        }
        return binding.getRoot();
    }

    @SuppressLint("NotifyDataSetChanged")
    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (MyApp.getInstance().getCurrentAct() instanceof RentItemNewPostActivity) {
            mActivity = (RentItemNewPostActivity) requireActivity();

            mPlanAdapter = new RentPaymentPlanAdapter(mActivity, paymentPlanList, (position, selPlanPos, list) -> {
                isClick = true;
                list.put("isCheck", "Yes");
                paymentPlanList.set(position, list);
                mActivity.mRentItemData.setiPaymentPlanId(paymentPlanList.get(position).get("iPaymentPlanId"));

                if (selPlanPos != position && selPlanPos != -1) {
                    HashMap<String, String> updateData = paymentPlanList.get(selPlanPos);
                    updateData.put("isCheck", "No");
                    paymentPlanList.set(selPlanPos, updateData);
                }

                mPlanAdapter.notifyDataSetChanged();
            });
        }
    }

    @Override
    public void onResume() {
        super.onResume();
        if (mActivity != null) {
            mActivity.selectServiceTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_ITEM_SELECT_PAYMENT_PLAN"));
        }
    }

    @SuppressLint("NotifyDataSetChanged")
    private void getPlan(GeneralFunctions generalFunc) {
        assert mActivity != null;

        binding.loading.setVisibility(View.VISIBLE);
        binding.rvRentPaymentPlan.setVisibility(View.GONE);

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getRentItemPaymentPlan");
        parameters.put("iUserId", generalFunc.getMemberId());
        parameters.put("iRentItemPostId", mActivity.mRentItemData.getiRentItemPostId() == null ? "" : mActivity.mRentItemData.getiRentItemPostId());
        parameters.put("eType", mActivity.eType);

        ApiHandler.execute(requireActivity(), parameters, (String responseString) -> {

            binding.loading.setVisibility(View.GONE);
            binding.rvRentPaymentPlan.setVisibility(View.VISIBLE);

            if (responseString != null && !responseString.equalsIgnoreCase("")) {
                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);
                if (isDataAvail) {
                    paymentPlanList.clear();

                    JSONArray messageArray = generalFunc.getJsonArray(Utils.message_str, responseString);
                    int selPos = -1;
                    if (messageArray != null) {

                        for (int i = 0; i < messageArray.length(); i++) {
                            JSONObject obj_tmp = generalFunc.getJsonObject(messageArray, i);

                            HashMap<String, String> mapData = new HashMap<>();
                            Iterator<String> keysItr = obj_tmp.keys();
                            while (keysItr.hasNext()) {
                                String key = keysItr.next();
                                String value = generalFunc.getJsonValueStr(key, obj_tmp);
                                mapData.put(key, value);
                                if (key.equalsIgnoreCase("isSelected")) {
                                    selPos = i;
                                }
                            }

                            paymentPlanList.add(mapData);
                        }
                    }

                    mPlanAdapter.notifyDataSetChanged();
                    new Handler(Looper.getMainLooper()).postDelayed(() -> mActivity.setPagerHeight(), 200);
                    if (selPos >= 0) {
                        int finalSelPos = selPos;
                        new Handler(Looper.getMainLooper()).postDelayed(() -> {
                            //
                            mPlanAdapter.setPositionClick(binding.rvRentPaymentPlan, finalSelPos);
                        }, 1000);
                    }
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });

    }

    public void checkPageNext() {
        if (mActivity != null) {
            if (Utils.checkText(mActivity.mRentItemData.getiPaymentPlanId()) && isClick) {
                mActivity.setPageNext();
            } else {
                mActivity.generalFunc.showMessage(mActivity.selectServiceTxt, mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_ITEM_SELECT_PAYMENT_PLAN_VALIDATION_MSG"));
            }
        }
    }
}