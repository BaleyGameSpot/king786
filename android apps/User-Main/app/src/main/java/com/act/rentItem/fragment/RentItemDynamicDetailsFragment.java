package com.act.rentItem.fragment;

import android.annotation.SuppressLint;
import android.content.Context;
import android.os.Bundle;
import android.os.Handler;
import android.text.InputType;
import android.view.LayoutInflater;
import android.view.MotionEvent;
import android.view.View;
import android.view.ViewGroup;
import android.view.inputmethod.EditorInfo;
import android.widget.LinearLayout;
import android.widget.ProgressBar;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;

import com.act.rentItem.RentItemNewPostActivity;
import com.dialogs.OpenListView;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.service.handler.ApiHandler;
import com.utils.Logger;
import com.utils.MyUtils;
import com.utils.Utils;
import com.view.MTextView;
import com.view.editBox.MaterialEditText;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;

public class RentItemDynamicDetailsFragment extends Fragment {

    @Nullable
    private RentItemNewPostActivity mActivity;
    public ProgressBar loading;
    private LinearLayout dynamicArea;
    private final ArrayList<JSONObject> objDataList = new ArrayList<>();
    private final ArrayList<Object> dataArrayList = new ArrayList<>();
    private String iCategoryId = "";
    private int fPosition;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {

        View view = inflater.inflate(R.layout.fragment_rent_item_dynamic_details, container, false);

        loading = view.findViewById(R.id.loading);
        loading.setVisibility(View.GONE);
        dynamicArea = view.findViewById(R.id.dynamicArea);
        dynamicArea.setVisibility(View.GONE);

        if (mActivity != null) {
            getFromField(mActivity.generalFunc);
        }

        return view;
    }

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (requireActivity() instanceof RentItemNewPostActivity activity) {
            mActivity = activity;
        }
    }

    @Override
    public void onResume() {
        super.onResume();
        if (mActivity != null) {
            if (mActivity.eType.equalsIgnoreCase("RentEstate")) {
                mActivity.selectServiceTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_PROPERTY_DETAIL"));
            } else if (mActivity.eType.equalsIgnoreCase("RentCars")) {
                mActivity.selectServiceTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_CAR_DETAILS"));
            } else {
                mActivity.selectServiceTxt.setText(mActivity.generalFunc.retrieveLangLBl("", "LBL_RENT_ITEM_DETAILS"));
            }
            getFromField(mActivity.generalFunc);
        }
    }

    private void getFromField(GeneralFunctions generalFunc) {
        assert mActivity != null;
        String mCategoryId = mActivity.mRentItemData.getiItemCategoryId();
        if (!Utils.checkText(mCategoryId)) {
            return;
        }
        if (mCategoryId.equalsIgnoreCase(iCategoryId)) {
            return;
        }

        iCategoryId = mCategoryId;

        loading.setVisibility(View.VISIBLE);

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getRentItemFormFields");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("iRentItemId", iCategoryId);
        parameters.put("iRentItemPostId", mActivity.mRentItemData.getiRentItemPostId() == null ? "" : mActivity.mRentItemData.getiRentItemPostId());
        parameters.put("eType", mActivity.eType);

        if (dynamicArea.getChildCount() > 0) {
            dynamicArea.removeAllViewsInLayout();
        }

        ApiHandler.execute(requireActivity(), parameters, (String responseString) -> {

            loading.setVisibility(View.GONE);

            if (responseString != null && !responseString.equalsIgnoreCase("")) {
                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseString);
                mActivity.isPickupAvailabilityRemove = generalFunc.getJsonValue("IsAvailibilitySectionShow", responseString).equalsIgnoreCase("Yes");
                if (mActivity != null) {
                    if (mActivity.mRentItemData.getisBuySell() != null && mActivity.mRentItemData.getisBuySell().equalsIgnoreCase("Yes")) {
                        mActivity.isEListing = true;
                    } else if (mActivity.generalFunc.getJsonValue("isBuySell", responseString).equalsIgnoreCase("Yes")) {
                        mActivity.isEListing = true;
                    }
                    mActivity.setToolSubTitle();
                }
                if (isDataAvail) {
                    addDynamicContentLayout(generalFunc.getJsonArray(Utils.message_str, responseString), generalFunc);
                    dynamicArea.setVisibility(View.VISIBLE);
                    mActivity.setPagerHeight();
                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                }
            } else {
                generalFunc.showError();
            }
        });
    }

    private void addDynamicContentLayout(JSONArray jobjArray, GeneralFunctions generalFunc) {
        if (!isAdded()) {
            return;
        }
        if (dynamicArea.getChildCount() > 0) {
            dynamicArea.removeAllViewsInLayout();
        }
        objDataList.clear();
        dataArrayList.clear();

        if (jobjArray != null && jobjArray.length() > 0) {
            for (int i = 0; i < jobjArray.length(); i++) {
                addDetailRow(generalFunc, i, generalFunc.getJsonObject(jobjArray, i));
            }
        }
    }

    @SuppressLint("ClickableViewAccessibility")
    private void addDetailRow(GeneralFunctions generalFunc, int pos, JSONObject jsonObject) {
        if (!(requireActivity() instanceof RentItemNewPostActivity)) {
            return;
        }
        if (!isAdded()) {
            return;
        }

        objDataList.add(jsonObject);

        LayoutInflater infalInflater = (LayoutInflater) requireActivity().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        @SuppressLint("InflateParams") View convertView = infalInflater.inflate(R.layout.item_rent_dynamic_layout, null);
        LinearLayout singleLineTextArea = convertView.findViewById(R.id.singleLineTextArea);
        singleLineTextArea.setVisibility(View.GONE);
        MTextView singleLineTitle = convertView.findViewById(R.id.singleLineTitle);
        MTextView singleLineRequired = convertView.findViewById(R.id.singleLineRequired);
        MaterialEditText singleLineTextField = convertView.findViewById(R.id.singleLineTextField);

        LinearLayout multiLineTextArea = convertView.findViewById(R.id.multiLineTextArea);
        multiLineTextArea.setVisibility(View.GONE);
        MaterialEditText multiLineText = convertView.findViewById(R.id.multiLineText);
        MTextView multiLineRequired = convertView.findViewById(R.id.multiLineRequired);
        MTextView multiLineTitle = convertView.findViewById(R.id.multiLineTitle);

        multiLineText.setHideUnderline(true);
        MyUtils.editBoxMultiLine(multiLineText);
        LinearLayout selectTextArea = convertView.findViewById(R.id.selectTextArea);
        selectTextArea.setVisibility(View.GONE);
        MTextView selectTitle = convertView.findViewById(R.id.selectTitle);
        MTextView selectRequired = convertView.findViewById(R.id.selectRequired);
        MaterialEditText selectBox = convertView.findViewById(R.id.selectBox);

        singleLineTextArea.setTag(pos);
        singleLineTitle.setTag(pos);
        singleLineTextField.setTag(pos);

        multiLineTextArea.setTag(pos);
        multiLineText.setTag(pos);

        selectTextArea.setTag(pos);
        selectBox.setTag(pos);

        String vFieldName = generalFunc.getJsonValueStr("vFieldName", jsonObject);
        String tDesc = generalFunc.getJsonValueStr("tDesc", jsonObject);
        String eInputType = generalFunc.getJsonValueStr("eInputType", jsonObject);
        String eAllowFloat = generalFunc.getJsonValueStr("eAllowFloat", jsonObject);
        String eRequired = generalFunc.getJsonValueStr("eRequired", jsonObject);
        String vAddedValue = generalFunc.getJsonValueStr("vAddedValue", jsonObject);

        if (eInputType.equalsIgnoreCase("Text") || eInputType.equalsIgnoreCase("Number")) {

            singleLineTextArea.setVisibility(View.VISIBLE);
            singleLineRequired.setVisibility(eRequired.equalsIgnoreCase("Yes") ? View.VISIBLE : View.GONE);

            singleLineTitle.setText(vFieldName);
            singleLineTextField.setHint(tDesc);
            singleLineTextField.setText(vAddedValue);

            if (eInputType.equalsIgnoreCase("Number")) {
                if (eAllowFloat.equalsIgnoreCase("Yes")) {
                    singleLineTextField.setInputType(InputType.TYPE_CLASS_NUMBER | InputType.TYPE_NUMBER_FLAG_SIGNED | InputType.TYPE_NUMBER_FLAG_DECIMAL);
                } else {
                    singleLineTextField.setInputType(InputType.TYPE_CLASS_NUMBER);
                }
            }

            dataArrayList.add(singleLineTextField);
            fPosition = pos;
        } else if (eInputType.equalsIgnoreCase("Textarea")) {
            multiLineTextArea.setVisibility(View.VISIBLE);
            multiLineRequired.setVisibility(eRequired.equalsIgnoreCase("Yes") ? View.VISIBLE : View.GONE);
            multiLineTitle.setText(vFieldName);
            multiLineText.setHint(tDesc);
            multiLineText.setText(vAddedValue);
            dataArrayList.add(multiLineText);
            fPosition = pos;
        } else if (eInputType.equalsIgnoreCase("Select")) {
            selectTextArea.setVisibility(View.VISIBLE);
            selectBox.setHint(tDesc);
            selectBox.setText(vAddedValue);
            Utils.removeInput(selectBox);

            selectTitle.setText(vFieldName);
            selectRequired.setVisibility(eRequired.equalsIgnoreCase("Yes") ? View.VISIBLE : View.GONE);

            dataArrayList.add(selectBox);
            selectBox.setOnTouchListener((view, motionEvent) -> {
                if (motionEvent.getAction() == MotionEvent.ACTION_UP && !view.hasFocus()) {
                    selectBox.performClick();
                }
                return true;
            });

            selectBox.setTag("select_" + generalFunc.getJsonValueStr("iSelectedOptioId", jsonObject));

            selectBox.setOnClickListener(view -> buildPackageTypes(generalFunc, selectBox, jsonObject, vFieldName));

        }
        //Detecting the last singleTextField or MultilineTextField & handled ImeOption

        singleLineTextField.setOnFocusChangeListener((view, hasFocus) -> {
            if (hasFocus) {
                if (view == ((View) dataArrayList.get(fPosition))) {
                    ((MaterialEditText) view).setImeOptions(EditorInfo.IME_ACTION_DONE);
                }
            }
        });
        singleLineTextField.setOnEditorActionListener((textView, actionId, keyEvent) -> {
            if (actionId == EditorInfo.IME_ACTION_DONE) {
                Utils.hideKeyboard(getActivity());
                return true;
            }
            return false;
        });

        dynamicArea.addView(convertView);
    }

    private void buildPackageTypes(GeneralFunctions generalFunc, MaterialEditText selectBox, JSONObject jsonObject, String vFieldName) {
        final ArrayList<HashMap<String, String>> packageTypeList = new ArrayList<>();
        String selectedOptionPos = Utils.getText(selectBox);
        int pos = -1;
        JSONArray arr_data = generalFunc.getJsonArray("Options", jsonObject);
        if (arr_data != null) {
            for (int i = 0; i < arr_data.length(); i++) {
                JSONObject obj_tmp = generalFunc.getJsonObject(arr_data, i);

                HashMap<String, String> hashMap = new HashMap<>();
                Iterator<String> keysItr = obj_tmp.keys();
                while (keysItr.hasNext()) {
                    String key = keysItr.next();
                    String value = generalFunc.getJsonValueStr(key, obj_tmp);
                    hashMap.put(key, value);
                    if (value.equalsIgnoreCase(selectedOptionPos)) {
                        pos = i;
                    }
                }
                packageTypeList.add(hashMap);
            }
            showPackageTypes(jsonObject, selectBox, packageTypeList, pos, vFieldName);
        }
    }

    private void showPackageTypes(JSONObject jsonObject, MaterialEditText selectBox, ArrayList<HashMap<String, String>> packageTypeList, int selectedOptionPos, String vFieldName) {
        assert mActivity != null;
        OpenListView.getInstance(mActivity, vFieldName, packageTypeList, OpenListView.OpenDirection.CENTER, true, position -> {
            HashMap<String, String> mapData = packageTypeList.get(position);
            String selectedVal = mapData.get("vTitle");
            selectBox.setText(selectedVal);
            selectBox.setTag("select_" + mapData.get("iOptionId"));

            if (mapData.containsKey("eListingTypeOptions")) {
                if (mapData.get("eListingTypeOptions").equalsIgnoreCase("Sale")) {
                    mActivity.isEListing = true;
                } else if (mapData.get("eListingTypeOptions").equalsIgnoreCase("Rent")) {
                    mActivity.isEListing = false;
                }
            }
        }).show(selectedOptionPos, "vTitle");
    }

    public void checkPageNext() {
        if (mActivity != null) {

            boolean allDetailsEntered = true;
            JSONArray jaStore = new JSONArray();
            for (int i = 0; i < dataArrayList.size(); i++) {

                JSONObject storeObj = new JSONObject();
                if (dataArrayList.get(i) instanceof MaterialEditText) {

                    if (mActivity.generalFunc.getJsonValueStr("eRequired", objDataList.get(i)).equalsIgnoreCase("Yes")) {
                        if (!Utils.checkText((MaterialEditText) dataArrayList.get(i))) {
                            allDetailsEntered = false;
                        }
                    }

                    try {
                        MaterialEditText editText = (MaterialEditText) dataArrayList.get(i);
                        if (Utils.checkText(editText)) {

                            String[] separated = editText.getTag().toString().split("_");
                            if (separated.length == 2) {
                                storeObj.put("iData", mActivity.generalFunc.getJsonValueStr("iRentFieldId", objDataList.get(i)));
                                storeObj.put("value", separated[1]);
                            } else {
                                storeObj.put("iData", mActivity.generalFunc.getJsonValueStr("iRentFieldId", objDataList.get(i)));
                                storeObj.put("value", Utils.getText(editText));
                            }
                        } else {
                            storeObj.put("iData", mActivity.generalFunc.getJsonValueStr("iRentFieldId", objDataList.get(i)));
                            storeObj.put("value", "");
                        }
                    } catch (JSONException e) {
                        Logger.e("Exception", "::" + e.getMessage());
                    }
                }
                jaStore.put(storeObj);
            }

            if (allDetailsEntered) {
                mActivity.mRentItemData.setDynamicDetailsArray(jaStore);
                new Handler().postDelayed(() -> mActivity.setPageNext(), 50);
            } else {
                mActivity.generalFunc.showMessage(mActivity.selectServiceTxt, mActivity.generalFunc.retrieveLangLBl("", "LBL_ENTER_REQUIRED_FIELDS"));
                mActivity.setPagerHeight();
            }
        }
    }
}