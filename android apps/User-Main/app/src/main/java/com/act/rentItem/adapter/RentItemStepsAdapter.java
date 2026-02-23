package com.act.rentItem.adapter;

import android.annotation.SuppressLint;
import android.graphics.Color;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;

import androidx.annotation.NonNull;
import androidx.core.content.ContextCompat;
import androidx.core.graphics.drawable.DrawableCompat;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.utils.Utils;
import com.view.MTextView;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class RentItemStepsAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final GeneralFunctions generalFanc;
    private final ArrayList<HashMap<String, String>> list;
    private final int gray, grayDark;
    private int selStepPos = 0;

    public RentItemStepsAdapter(GeneralFunctions generalFunc, ArrayList<HashMap<String, String>> list) {
        this.generalFanc = generalFunc;
        this.list = list;
        this.gray = Color.parseColor("#D4D2D3");
        this.grayDark = Color.parseColor("#A7A7A7");
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        View itemView = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_rent_step_view, parent, false);
        itemView.getLayoutParams().width = (int) (Utils.getScreenPixelWidth(parent.getContext()) / 3);
        return new HorizontalViewHolder(itemView);
    }

    @SuppressLint({"RecyclerView", "SetTextI18n"})
    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {
        HashMap<String, String> mapData = list.get(position);

        HorizontalViewHolder horizontalVH = (HorizontalViewHolder) holder;

        horizontalVH.txtNumber.setText(generalFanc.convertNumberWithRTL(mapData.get("vNumber")));
        horizontalVH.txtStep.setText(mapData.get("vTitle") + " " + generalFanc.convertNumberWithRTL(mapData.get("vNumber")));

        ///////
        if (mapData.containsKey("selPos")) {
            if (Utils.checkText(mapData.get("selPos"))) {
                selStepPos = Integer.parseInt(Objects.requireNonNull(mapData.get("selPos")));
            } else {
                selStepPos = 0;
            }
        }


        horizontalVH.txtNumber.setVisibility(View.VISIBLE);
        horizontalVH.imgDone.setVisibility(View.GONE);

        if (selStepPos == position) {

            DrawableCompat.setTint(horizontalVH.imgCircular.getDrawable().mutate(), ContextCompat.getColor(horizontalVH.itemView.getContext(), R.color.appThemeColor_1));
            horizontalVH.txtNumber.setTextColor(horizontalVH.itemView.getContext().getResources().getColor(R.color.white));
            horizontalVH.txtStep.setTextColor(horizontalVH.itemView.getContext().getResources().getColor(R.color.appThemeColor_1));
        } else {
            DrawableCompat.setTint(horizontalVH.imgCircular.getDrawable().mutate(), gray);
            horizontalVH.txtNumber.setTextColor(grayDark);
            horizontalVH.txtStep.setTextColor(grayDark);

            if (selStepPos < 0) {
                horizontalVH.txtNumber.setVisibility(View.GONE);
                horizontalVH.imgDone.setVisibility(View.VISIBLE);
                DrawableCompat.setTint(horizontalVH.imgDone.getDrawable().mutate(), ContextCompat.getColor(horizontalVH.itemView.getContext(), R.color.white));
                DrawableCompat.setTint(horizontalVH.imgCircular.getDrawable().mutate(), ContextCompat.getColor(horizontalVH.itemView.getContext(), R.color.appThemeColor_1));
                horizontalVH.txtStep.setTextColor(horizontalVH.itemView.getContext().getResources().getColor(R.color.appThemeColor_1));
            }
        }
        ///////

        horizontalVH.viewLine.setBackgroundColor(grayDark);
        if (position == (getItemCount() - 1)) {
            horizontalVH.viewLine.setVisibility(View.GONE);
        } else {
            horizontalVH.viewLine.setVisibility(View.VISIBLE);
        }
    }

    @Override
    public int getItemCount() {
        return list.size();
    }

    private static class HorizontalViewHolder extends RecyclerView.ViewHolder {

        private final ImageView imgCircular, imgDone;
        private final MTextView txtNumber, txtStep;
        private final View viewLine;

        private HorizontalViewHolder(View itemView) {
            super(itemView);

            imgCircular = itemView.findViewById(R.id.imgCircular);
            imgDone = itemView.findViewById(R.id.imgDone);
            txtNumber = itemView.findViewById(R.id.txtNumber);
            txtStep = itemView.findViewById(R.id.txtStep);
            viewLine = itemView.findViewById(R.id.viewLine);
        }
    }
}