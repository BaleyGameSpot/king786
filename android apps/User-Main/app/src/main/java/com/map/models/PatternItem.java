package com.map.models;

import com.google.android.gms.maps.model.Gap;

public class PatternItem {
    public enum Pattern {
        Gap,
        Dash
    }

    public float length = 2;
    public Pattern patternType;

    public PatternItem(Pattern patternType, float length) {
        this.patternType = patternType;
        this.length = length;
    }

    public static PatternItem Gap(float length) {
        return new PatternItem(Pattern.Gap, length);
    }

    public static PatternItem Dash(float length) {
        return new PatternItem(Pattern.Dash, length);
    }
}
