package com.dialogs;

import android.content.Context;
import android.view.View;
import android.widget.ImageView;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatDialog;
import androidx.cardview.widget.CardView;
import androidx.dynamicanimation.animation.DynamicAnimation;
import androidx.dynamicanimation.animation.SpringAnimation;
import androidx.dynamicanimation.animation.SpringForce;
import androidx.recyclerview.widget.RecyclerView;

import com.adapter.files.FilterDialogListAdapter;
import com.general.files.GeneralFunctions;
import com.google.android.material.bottomsheet.BottomSheetBehavior;
import com.google.android.material.bottomsheet.BottomSheetDialog;
import com.buddyverse.main.R;
import com.utils.LayoutDirection;
import com.utils.Utils;
import com.view.MTextView;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class OpenBottomListView {
    public static AppCompatDialog mListDialog = null;

    public enum OpenDirection {CENTER, BOTTOM}

    private Context mContext;
    private String title;
    private OpenDirection openDirection;
    private boolean isCancelable;
    private ArrayList<HashMap<String, String>> arrayList;
    private OpenListView.OnItemClickList onItemClickList;
    private AppCompatDialog listDialog = null;

    GeneralFunctions generalFunctions;

    private OpenBottomListView(Context mContext, String title, ArrayList<HashMap<String, String>> arrayList, OpenDirection openDirection, boolean isCancelable, OpenListView.OnItemClickList onItemClickList) {
        this.mContext = mContext;
        this.title = title;
        this.openDirection = openDirection;
        this.isCancelable = isCancelable;
        this.arrayList = arrayList;
        this.onItemClickList = onItemClickList;
        generalFunctions = new GeneralFunctions(mContext);
    }

    public void show(int selectedItemPosition, String keyToShowAsTitle) {
        if (mListDialog != null && mListDialog.isShowing()) {
            return;
        }
        if (openDirection == OpenDirection.BOTTOM) {
            final BottomSheetDialog dialog = new BottomSheetDialog(mContext);
            listDialog = dialog;
        }

        if (openDirection == OpenDirection.CENTER) {
            AppCompatDialog dialog = new AppCompatDialog(mContext);
            Objects.requireNonNull(dialog.getWindow()).getAttributes().windowAnimations = R.style.DialogAnimation;
            listDialog = dialog;
        }
        mListDialog = listDialog;

        showDialogWithImage(selectedItemPosition, keyToShowAsTitle);

    }

    private void showDialogWithImage(int selectedItemPosition, String keyToShowAsTitle) {
        if (listDialog == null) {
            return;
        }
        final BottomSheetDialog optionDailog = new BottomSheetDialog(mContext);
        mListDialog = optionDailog;
        View contentView = View.inflate(mContext, R.layout.filter_trasaction_history, null);
        optionDailog.setContentView(contentView);
        BottomSheetBehavior mBehavior = BottomSheetBehavior.from((View) contentView.getParent());
        mBehavior.setPeekHeight(Utils.dpToPx(600, mContext));
        optionDailog.setCancelable(false);
        View bottomSheetView = optionDailog.getWindow().getDecorView().findViewById(R.id.design_bottom_sheet);
        bottomSheetView.setBackgroundColor(mContext.getResources().getColor(android.R.color.transparent));

        CardView cardshadow = bottomSheetView.findViewById(R.id.cardshadow);

        FilterDialogListAdapter listAdapter;
        listAdapter = new FilterDialogListAdapter(mContext, arrayList, selectedItemPosition, keyToShowAsTitle, generalFunctions);


        ImageView closeImg = (ImageView) bottomSheetView.findViewById(R.id.closeImg);
        MTextView titleTxt = (MTextView) bottomSheetView.findViewById(R.id.TitleTxt);
        MTextView subtitleTxt = (MTextView) bottomSheetView.findViewById(R.id.subTitleTxt);
        titleTxt.setText(title);
        if (!subtitleTxt.getText().equals("")) {
            subtitleTxt.setVisibility(View.VISIBLE);
        } else {
            subtitleTxt.setVisibility(View.GONE);
        }

        SpringAnimation animation = new SpringAnimation(bottomSheetView, DynamicAnimation.TRANSLATION_Y);
        SpringForce spring = new SpringForce();
        spring.setFinalPosition(Utils.dpToPx(15, mContext));
        spring.setStiffness(SpringForce.STIFFNESS_LOW);
        spring.setDampingRatio(SpringForce.DAMPING_RATIO_HIGH_BOUNCY);
        // animation.setStartVelocity(1500);
        animation.setSpring(spring);

        closeImg.setOnClickListener(v -> {
            optionDailog.dismiss();
            mListDialog = null;
        });

        RecyclerView mRecyclerView = bottomSheetView.findViewById(R.id.mRecyclerView);
        mRecyclerView.setAdapter(listAdapter);
        mRecyclerView.scrollToPosition(selectedItemPosition);

        mRecyclerView.addOnScrollListener(new RecyclerView.OnScrollListener() {
            @Override
            public void onScrollStateChanged(@NonNull RecyclerView recyclerView, int newState) {
                super.onScrollStateChanged(recyclerView, newState);
            }

            @Override
            public void onScrolled(@NonNull RecyclerView recyclerView, int dx, int dy) {
                super.onScrolled(recyclerView, dx, dy);

                if (!recyclerView.canScrollVertically(-1)) {
                    // we have reached the top of the list
                    cardshadow.setVisibility(View.GONE);
                } else {
                    // we are not at the top yet
                    cardshadow.setVisibility(View.VISIBLE);
                }
            }
        });
        listAdapter.setOnItemClickList(position -> {
            optionDailog.dismiss();
            if (onItemClickList != null) {
                onItemClickList.onItemClick(position);
            }
            onItemClickList = null;
            closeImg.setClickable(false);
            optionDailog.setCancelable(false);
            mListDialog = null;
        });
        LayoutDirection.setLayoutDirection(optionDailog);
        try {
            if (!optionDailog.isShowing()) {
                optionDailog.show();
                animation.start();
            }
        } catch (Exception e) {

        }
    }


    public static OpenBottomListView getInstance(Context mContext, String title, ArrayList<HashMap<String, String>> arrayList, OpenDirection openDirection, boolean isCancelable, OpenListView.OnItemClickList onItemClickList, boolean isImageinList, String subTitleTxt, boolean isloading) {
        return new OpenBottomListView(mContext, title, arrayList, openDirection, isCancelable, onItemClickList);
    }
}
