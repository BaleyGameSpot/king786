package com.viewholder;

import android.content.Context;
import android.graphics.Paint;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;

import com.buddyverse.main.R;
import com.general.files.GeneralFunctions;
import com.model.RestaurantCataChildModel;
import com.utils.LoadImage;
import com.view.MTextView;

public class RestaurntCataChildViewHolder extends ChildViewHolder {

    private MTextView tvDesc;
    private MTextView title, desc, price, offerPrice;
    public LinearLayout restaurantAdptrLayout;
    ImageView itemImage;
    Context mContext;
    ImageView vegImage, nonVegImage;
    BiodataExpandable.ChildItemClickListener itemChildClickListener;


    public RestaurntCataChildViewHolder(View itemView, Context mContext, BiodataExpandable.ChildItemClickListener itemChildClickListener) {
        super(itemView);

        this.mContext = mContext;
        this.itemChildClickListener = itemChildClickListener;
        title = (MTextView) itemView.findViewById(R.id.title);
        desc = (MTextView) itemView.findViewById(R.id.desc);
        price = (MTextView) itemView.findViewById(R.id.price);
        offerPrice = (MTextView) itemView.findViewById(R.id.offerPrice);
        itemImage = (ImageView) itemView.findViewById(R.id.itemImage);
        vegImage = (ImageView) itemView.findViewById(R.id.vegImage);
        nonVegImage = (ImageView) itemView.findViewById(R.id.nonVegImage);
        restaurantAdptrLayout = (LinearLayout) itemView.findViewById(R.id.restaurantAdptrLayout);
    }

    public void bind(RestaurantCataChildModel biodataChildModel) {


        LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(
                LinearLayout.LayoutParams.MATCH_PARENT,
                LinearLayout.LayoutParams.WRAP_CONTENT
        );


        title.setText(biodataChildModel.getvItemType());
        if (biodataChildModel.getvItemDesc() != null && !biodataChildModel.getvItemDesc().equalsIgnoreCase("")) {
            desc.setText(biodataChildModel.getvItemDesc());
            desc.setVisibility(View.VISIBLE);
        } else {
            desc.setVisibility(View.GONE);
        }

        restaurantAdptrLayout.setLayoutParams(params);


        if (GeneralFunctions.parseDoubleValue(0, biodataChildModel.getfOfferAmt()) > 0) {
            price.setText(biodataChildModel.getStrikeoutPrice());
            price.setPaintFlags(price.getPaintFlags() | Paint.STRIKE_THRU_TEXT_FLAG);
            offerPrice.setText(biodataChildModel.getfDiscountPricewithsymbol());
            offerPrice.setVisibility(View.VISIBLE);
        } else {
            price.setText(biodataChildModel.getStrikeoutPrice());
            price.setPaintFlags(0);
            offerPrice.setVisibility(View.GONE);
        }

        if (biodataChildModel.geteFoodType().equalsIgnoreCase("NonVeg")) {
            nonVegImage.setVisibility(View.VISIBLE);
            vegImage.setVisibility(View.GONE);
        } else if (biodataChildModel.geteFoodType().equalsIgnoreCase("Veg")) {
            vegImage.setVisibility(View.VISIBLE);
            nonVegImage.setVisibility(View.GONE);
        }

        if (biodataChildModel.getvImage() != null && !biodataChildModel.getvImage().equals("")) {
            itemImage.setVisibility(View.VISIBLE);

            new LoadImage.builder(LoadImage.bind(biodataChildModel.getvImage()), itemImage).setErrorImagePath(R.color.imageBg).setPlaceholderImagePath(R.color.imageBg).build();
        } else {
            itemImage.setVisibility(View.GONE);
        }
        restaurantAdptrLayout.setOnClickListener(v -> {
            //Toast.makeText(mContext, "Hello", Toast.LENGTH_SHORT).show();
            itemChildClickListener.setOnClick(0, biodataChildModel);

        });


    }


}
