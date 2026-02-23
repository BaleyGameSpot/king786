package com.act;

import android.content.Intent;
import android.os.Bundle;
import android.text.Html;
import android.view.View;
import android.widget.LinearLayout;

import androidx.databinding.DataBindingUtil;

import com.activity.ParentActivity;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityInformationBinding;
import com.utils.LoadImage;
import com.utils.Utils;
import com.view.MButton;
import com.view.MaterialRippleLayout;

import org.json.JSONException;
import org.json.JSONObject;

public class InformationActivity extends ParentActivity {
    private ActivityInformationBinding binding;
    private JSONObject dataObject;
    private MButton okBtn;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_information);

        try {
            dataObject = new JSONObject(getIntent().getStringExtra("serviceDataObject"));
        } catch (JSONException e) {
            throw new RuntimeException(e);
        }
//        if (dataObject == null) {
//            return;
//        }

        initViews();
    }

    private void initViews() {

        String vImageHeight = generalFunc.getJsonValueStr("vImageHeight", dataObject);
        String vImageWidth = generalFunc.getJsonValueStr("vImageWidth", dataObject);
        double ratio = GeneralFunctions.parseDoubleValue(0.0, String.valueOf(vImageWidth)) / GeneralFunctions.parseDoubleValue(0.0, String.valueOf(vImageHeight));

        int sWidth = (int) Utils.getScreenPixelWidth(this) - getResources().getDimensionPixelSize(R.dimen._30sdp);
        int sHeight = (int) (sWidth / ratio);

        LinearLayout.LayoutParams mParams = (LinearLayout.LayoutParams) binding.infoImg.getLayoutParams();
        mParams.width = sWidth;
        mParams.height = sHeight;
        binding.infoImg.setLayoutParams(mParams);

        String Url = generalFunc.getJsonValueStr("vPageImg", dataObject);

        if (!Utils.checkText(Url)) {
            Url = "Temp";
        }
        Url = Utils.getResizeImgURL(this, Url, sWidth, sHeight);
        new LoadImage.builder(LoadImage.bind(Url), binding.infoImg).setPlaceholderImagePath(R.color.imageBg).setErrorImagePath(R.color.imageBg).build();

        addToClickHandler(binding.closeImg);

        okBtn = ((MaterialRippleLayout) findViewById(R.id.okBtn)).getChildView();
        okBtn.setId(Utils.generateViewId());
        addToClickHandler(okBtn);

        binding.infoTitleTxt.setText(generalFunc.getJsonValueStr("vPageTitle", dataObject));
        binding.infoTitleSubTxt.setText(Html.fromHtml(generalFunc.getJsonValueStr("vPageDesc", dataObject)));
        okBtn.setText(generalFunc.getJsonValueStr("vPageBtn", dataObject));
    }

    public void onClick(View view) {
        int i = view.getId();
        if (i == binding.closeImg.getId()) {
            onBackPressed();
        } else if (i == okBtn.getId()) {
            Intent intent = new Intent();
            intent.putExtra("serviceDataObject", dataObject.toString());
            setResult(-1, intent);
            finish();
        }
    }
}