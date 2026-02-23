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
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.MyApp;
import com.buddyverse.main.R;
import com.act.rentItem.RentItemNewPostActivity;
import com.act.rentItem.adapter.RentCategorySubCategoryAdapter;
import com.utils.Utils;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class RentSubCategoryFragment extends Fragment {

    @Nullable
    private RentItemNewPostActivity mActivity;
    private RentCategorySubCategoryAdapter categoryAdapter;
    public ArrayList<HashMap<String, String>> subCategoryList = new ArrayList<>();

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {

        View view = inflater.inflate(R.layout.fragment_rent_sub_category, container, false);

        RecyclerView rvRentItemNewPost = view.findViewById(R.id.rvRentItemNewPost);
        rvRentItemNewPost.setAdapter(categoryAdapter);

        return view;
    }

    @Override
    public void onResume() {
        super.onResume();
        if (mActivity == null) {
            return;
        }
        dataLoad();
        new Handler(Looper.getMainLooper()).postDelayed(() -> mActivity.setPagerHeight(), 200);
    }

    @SuppressLint("NotifyDataSetChanged")
    private void dataLoad() {
        if (mActivity == null) {
            return;
        }
        if (mActivity.isFirst) {
            mActivity.selectServiceTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_SELECT_SUB_CATEGORY"));
        }
        mActivity.isFirst = true;

        if (mActivity.mCategoriesArr != null) {
            subCategoryList.clear();
            boolean resetSubCatPos = true;
            for (int k = 0; k < mActivity.mCategoriesArr.length(); k++) {
                JSONArray rentSubCategoriesArr = mActivity.generalFunc.getJsonArray("SubCategory", mActivity.generalFunc.getJsonObject(mActivity.mCategoriesArr, k));
                if (rentSubCategoriesArr != null && rentSubCategoriesArr.length() > 0) {
                    for (int j = 0; j < rentSubCategoriesArr.length(); j++) {

                        JSONObject objRentSubCategory = mActivity.generalFunc.getJsonObject(rentSubCategoriesArr, j);
                        HashMap<String, String> map = new HashMap<>();

                        map.put("showType", "LIST");
                        map.put("iCategoryId", mActivity.generalFunc.getJsonValueStr("iCategoryId", objRentSubCategory));
                        map.put("iSubCategoryId", mActivity.generalFunc.getJsonValueStr("iSubCategoryId", objRentSubCategory));
                        map.put("vTitle", mActivity.generalFunc.getJsonValueStr("vTitle", objRentSubCategory));
                        map.put("vImage", mActivity.generalFunc.getJsonValueStr("vImage", objRentSubCategory));
                        if (mActivity.generalFunc.getJsonValueStr("iCategoryId", objRentSubCategory).equalsIgnoreCase(mActivity.mRentItemData.getiItemCategoryId())) {
                            subCategoryList.add(map);
                            if (Utils.checkText(mActivity.mRentItemData.getiItemSubCategoryId())) {
                                if (mActivity.generalFunc.getJsonValueStr("iSubCategoryId", objRentSubCategory).equalsIgnoreCase(mActivity.mRentItemData.getiItemSubCategoryId())) {
                                    map.put("isCheck", "Yes");
                                    resetSubCatPos = false;
                                }
                            }
                        }
                    }
                }
            }
            if (resetSubCatPos) {
                categoryAdapter.resetSubCatPos();
            }
            categoryAdapter.notifyDataSetChanged();
        }
    }

    @SuppressLint("NotifyDataSetChanged")
    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (MyApp.getInstance().getCurrentAct() instanceof RentItemNewPostActivity) {
            mActivity = (RentItemNewPostActivity) requireActivity();

            categoryAdapter = new RentCategorySubCategoryAdapter(requireActivity(), mActivity.generalFunc, subCategoryList, new RentCategorySubCategoryAdapter.OnItemClickListener() {
                @Override
                public void onCategoryClickList(int position, int selCatPos) {

                }

                @Override
                public void onSubCategoryClickList(int position, int selSubCatPos) {
                    HashMap<String, String> updateData1 = subCategoryList.get(position);
                    updateData1.put("isCheck", "Yes");
                    subCategoryList.set(position, updateData1);

                    if (selSubCatPos != position && selSubCatPos != -1) {
                        HashMap<String, String> updateData = subCategoryList.get(selSubCatPos);
                        updateData.put("isCheck", "No");
                        subCategoryList.set(selSubCatPos, updateData);
                    }
                    mActivity.mRentItemData.setiItemSubCategoryId(subCategoryList.get(position).get("iSubCategoryId"));
                    categoryAdapter.notifyDataSetChanged();
                    checkPageNext();
                }
            });
            dataLoad();
        }
    }

    public void checkPageNext() {
        if (mActivity != null) {
            if (Utils.checkText(mActivity.mRentItemData.getiItemSubCategoryId())) {
                mActivity.setPageNext();
            } else {
                mActivity.generalFunc.showMessage(mActivity.selectServiceTxt, mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_SELECT_SUB_CATEGORY_VALIDATION_MSG"));

            }
        }
    }
}