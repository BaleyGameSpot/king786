package com.act.rentItem.fragment;

import android.annotation.SuppressLint;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.RecyclerView;

import com.act.rentItem.RentItemNewPostActivity;
import com.act.rentItem.adapter.RentCategorySubCategoryAdapter;
import com.general.files.MyApp;
import com.general.files.SpacesItemDecoration;
import com.buddyverse.main.R;
import com.utils.Utils;

import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class RentCategoryFragment extends Fragment {

    @Nullable
    private RentItemNewPostActivity mActivity;
    private RentCategorySubCategoryAdapter categoryAdapter;
    private final ArrayList<HashMap<String, String>> categoryList = new ArrayList<>();

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {

        View view = inflater.inflate(R.layout.fragment_rent_category, container, false);

        RecyclerView rvRentItemNewPost = view.findViewById(R.id.rvRentItemNewPost);
        rvRentItemNewPost.setAdapter(categoryAdapter);
        rvRentItemNewPost.addItemDecoration(new SpacesItemDecoration(3, 20, true));
        return view;
    }

    @SuppressLint("NotifyDataSetChanged")
    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (MyApp.getInstance().getCurrentAct() instanceof RentItemNewPostActivity) {
            mActivity = (RentItemNewPostActivity) requireActivity();

            categoryList.clear();
            if (mActivity.mCategoriesArr != null && mActivity.mCategoriesArr.length() > 0) {
                for (int k = 0; k < mActivity.mCategoriesArr.length(); k++) {
                    JSONObject objRentCategory = mActivity.generalFunc.getJsonObject(mActivity.mCategoriesArr, k);
                    HashMap<String, String> map = new HashMap<>();

                    map.put("showType", "GRID");
                    if (Utils.checkText(mActivity.mRentItemData.getiItemCategoryId())) {
                        if (mActivity.generalFunc.getJsonValueStr("iCategoryId", objRentCategory).equalsIgnoreCase(mActivity.mRentItemData.getiItemCategoryId())) {
                            map.put("isCheck", "Yes");
                        }
                    }

                    map.put("iCategoryId", mActivity.generalFunc.getJsonValueStr("iCategoryId", objRentCategory));
                    map.put("vTitle", mActivity.generalFunc.getJsonValueStr("vTitle", objRentCategory));
                    map.put("vImage", mActivity.generalFunc.getJsonValueStr("vImage", objRentCategory));
                    map.put("vImage1", mActivity.generalFunc.getJsonValueStr("vImage1", objRentCategory));

                    categoryList.add(map);
                }
            }

            if (categoryAdapter == null) {
                categoryAdapter = new RentCategorySubCategoryAdapter(requireActivity(), mActivity.generalFunc, categoryList, new RentCategorySubCategoryAdapter.OnItemClickListener() {

                    @Override
                    public void onCategoryClickList(int position, int selCatPos) {

                        HashMap<String, String> updateData1 = categoryList.get(position);
                        updateData1.put("isCheck", "Yes");
                        categoryList.set(position, updateData1);

                        if (selCatPos != position && selCatPos != -1) {
                            HashMap<String, String> updateData = categoryList.get(selCatPos);
                            updateData.put("isCheck", "No");
                            categoryList.set(selCatPos, updateData);
                            mActivity.mRentItemData.setiItemSubCategoryId(null);
                        }
                        mActivity.mRentItemData.setiItemCategoryId(updateData1.get("iCategoryId"));
                        categoryAdapter.notifyDataSetChanged();
                        checkPageNext();
                    }

                    @Override
                    public void onSubCategoryClickList(int position, int selSubCatPos) {

                    }
                });
            } else {
                categoryAdapter.notifyDataSetChanged();
            }
        }
    }

    @Override
    public void onResume() {
        super.onResume();
        if (mActivity == null) {
            return;
        }
        if (mActivity.eType.equalsIgnoreCase("RentEstate")) {
            mActivity.selectServiceTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_ESTATE_PROPERTY_TYPE"));
        } else if (mActivity.eType.equalsIgnoreCase("RentCars")) {
            mActivity.selectServiceTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_SELECT_CAR_TYPE"));
        } else {
            mActivity.selectServiceTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_SELECT_CATEGORY"));
        }
    }

    public void checkPageNext() {
        if (mActivity != null) {
            if (Utils.checkText(mActivity.mRentItemData.getiItemCategoryId())) {
                mActivity.setPageNext();
            } else {
                mActivity.generalFunc.showMessage(mActivity.selectServiceTxt, mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_SELECT_CATEGORY_VALIDATION_MSG"));
            }
        }
    }
}