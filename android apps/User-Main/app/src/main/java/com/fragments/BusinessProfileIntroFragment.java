package com.fragments;

import android.os.Bundle;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;

import com.act.BusinessProfileActivity;
import com.act.BusinessSetupActivity;
import com.buddyverse.main.R;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.utils.LoadImage;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;

import java.util.HashMap;

public class BusinessProfileIntroFragment extends BaseFragment {

    View v;
    MButton btn_type2;
    MTextView noteHeading;
    MTextView noteTxt;
    ImageView introImage;

    HashMap<String, String> map;
    BusinessProfileActivity businessProfileActivity;
    GeneralFunctions generalFunc;

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        v = inflater.inflate(R.layout.fragment_business_profile_intro, container, false);

        businessProfileActivity = (BusinessProfileActivity) getActivity();

        generalFunc = businessProfileActivity.generalFunc;
        btn_type2 = ((MaterialRippleLayout) v.findViewById(R.id.btn_type2)).getChildView();
        introImage = ((ImageView) v.findViewById(R.id.introImage));
        noteHeading = (MTextView) v.findViewById(R.id.noteHeading);
        noteTxt = (MTextView) v.findViewById(R.id.noteTxt);
        addToClickHandler(noteTxt);
        btn_type2.setId(Utils.generateViewId());
        addToClickHandler(btn_type2);
        map = (HashMap<String, String>) getArguments().get("Data");

        noteHeading.setText(map.get("vScreenTitle"));
        noteTxt.setText(map.get("tDescription"));
        btn_type2.setText(map.get("vScreenButtonText"));

        String vWelcomeImage = map.get("vWelcomeImage");
        if (!vWelcomeImage.equalsIgnoreCase("")) {
            new LoadImage.builder(LoadImage.bind(vWelcomeImage), introImage).build();
        }
        return v;
    }


    public void onClickView(View view) {
        int i = view.getId();
        if (i == btn_type2.getId()) {
            Bundle bn = new Bundle();
            bn.putString("iUserProfileMasterId", map.get("iUserProfileMasterId"));
            new ActUtils(getContext()).startActWithData(BusinessSetupActivity.class, bn);
        }
    }

}
