package com.model;

import java.io.Serializable;

/**
 * Created by putuguna on 04/01/17.
 */

public class TransactionTypesModel implements Serializable {

    private final int imageId;
    private String name;

    public int getCatType() {
        return catType;
    }

    public void setCatType(int catType) {
        this.catType = catType;
    }

    private int catType;
    private Boolean isSelected;

    public Boolean getSelected() {
        return isSelected;
    }

    public void setSelected(Boolean selected) {
        isSelected = selected;
    }

    public int getId() {
        return imageId;
    }

    /*public void setId(String id) {
        this.imageId = imageId;
    }*/

    public TransactionTypesModel(int imageId, String name, Boolean isSelected, int catType) {
        this.imageId = imageId;
        this.name = name;
        this.isSelected = isSelected;
        this.catType = catType;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }


}
